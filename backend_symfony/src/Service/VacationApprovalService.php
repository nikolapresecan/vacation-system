<?php
namespace App\Service;

use App\Entity\Request;
use App\Repository\TeamEmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use SplPriorityQueue;

class VacationApprovalService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TeamEmployeeRepository $teamEmployeeRepo,
        private float $ratio = 0.40 
    ) {}
    
    public function priorityScore(Request $r): float
    {
        $e = $r->getEmployee();

        $years = $e->getServiceYears() ?? 0; 
        
        $roles = $e->getRoles();
        $roleWeight = 0.0;
        if (in_array('ROLE_TEAM LEADER', $roles, true)) {
            $roleWeight += 2.0;
        }
        if (in_array('ROLE_PROJECT MANAGER', $roles, true)) {
            $roleWeight += 1.0;
        }

        $submittedTs = $r->getCreatedDate()?->getTimestamp() ?? time();
        $early = 1 / max(1, (time() - $submittedTs) / 86400.0); 

        return 2.0 * $years + 1.5 * $roleWeight + 0.25 * $early;
    }
    
    public function buildPriorityQueue(array $requests): SplPriorityQueue
    {
        $q = new SplPriorityQueue();
        $q->setExtractFlags(SplPriorityQueue::EXTR_DATA); 
        foreach ($requests as $r) {
            $q->insert($r, $this->priorityScore($r));
        }
        return $q;
    }
    
    public function enforceCapacityOrThrow(Request $candidate): void
    {
        $res = $this->capacityCheck($candidate);
        if (!$res['ok']) {
            throw new \RuntimeException($res['reason']);
        }
    }

    public function capacityCheck(Request $candidate): array
    {
        $teamId = $candidate->getTeam()->getId();
        $start  = $candidate->getStartDate();
        $end    = $candidate->getEndDate();

        $teamSize        = $this->teamSize($teamId);
        $approvedNow     = $this->countApprovedOverlaps($teamId, $start, $end);
        $maxAbsent       = max(1, (int) floor($this->ratio * $teamSize));
        $absentIfApprove = $approvedNow + 1;

        if ($absentIfApprove <= $maxAbsent) {
            return ['ok' => true, 'teamSize' => $teamSize, 'maxAbsent' => $maxAbsent];
        }
        return [
            'ok' => false,
            'reason' => sprintf('Premašuje kapacitet: %d/%d (max %d).', $absentIfApprove, $teamSize, $maxAbsent),
            'teamSize' => $teamSize,
            'maxAbsent' => $maxAbsent
        ];
    }
    
    public function chooseOptimalSubset(array $requests): array
    {
        usort($requests, fn($a,$b) => $a->getEndDate() <=> $b->getEndDate());
        $n = count($requests);
        if ($n === 0) return [];

        $w = array_map(fn($r) => $this->priorityScore($r), $requests);
        
        $p = array_fill(0, $n, -1);
        for ($j = 0; $j < $n; $j++) {
            $p[$j] = $this->findNonOverlapIndex($requests, $j);
        }
        
        $dp = array_fill(0, $n, 0.0);
        for ($j = 0; $j < $n; $j++) {
            $incl = $w[$j] + ($p[$j] >= 0 ? $dp[$p[$j]] : 0.0);
            $excl = $j > 0 ? $dp[$j - 1] : 0.0;
            $dp[$j] = max($incl, $excl);
        }
        
        $res = [];
        for ($j = $n - 1; $j >= 0; ) {
            $incl = $w[$j] + ($p[$j] >= 0 ? $dp[$p[$j]] : 0.0);
            $excl = $j > 0 ? $dp[$j - 1] : 0.0;
            if ($incl >= $excl) { $res[] = $requests[$j]; $j = $p[$j]; }
            else { $j--; }
        }

        return array_reverse($res);
    }
    
    private function teamSize(int $teamId): int
    {
        $te = $this->teamEmployeeRepo->findOneBy(['team' => $teamId]);
        if (!$te) return 0;
        $members = method_exists($te, 'getEmployees') ? $te->getEmployees() : [];
        return is_countable($members) ? count($members) : 0;
    }
    
    private function countApprovedOverlaps(int $teamId, \DateTimeInterface $start, \DateTimeInterface $end): int
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('COUNT(r.id)')
           ->from('App\Entity\Request', 'r')
           ->join('r.status', 's')
           ->where('r.team = :teamId')
           ->andWhere('s.name = :approved')
           ->andWhere('r.startDate <= :end')
           ->andWhere('r.endDate   >= :start')
           ->setParameters([
               'teamId'   => $teamId,
               'approved' => 'APPROVED',
               'start'    => $start->format('Y-m-d'),
               'end'      => $end->format('Y-m-d'),
           ]);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
    
    private function findNonOverlapIndex(array $reqs, int $j): int
    {
        $lo = 0; $hi = $j - 1; $ans = -1;
        $startJ = $reqs[$j]->getStartDate();
        while ($lo <= $hi) {
            $mid = intdiv($lo + $hi, 2);
            if ($reqs[$mid]->getEndDate() < $startJ) { $ans = $mid; $lo = $mid + 1; }
            else { $hi = $mid - 1; }
        }
        return $ans;
    }
}
