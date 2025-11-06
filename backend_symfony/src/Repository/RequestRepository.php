<?php

namespace App\Repository;

use App\Entity\Request;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Request>
 */
class RequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Request::class);
    }

    public function findByStatusName(string $statusName): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.status', 's')
            ->where('s.name = :statusName')
            ->setParameter('statusName', $statusName)
            ->orderBy('r.createdDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByStatusNameAndTeamId(string $statusName, int $teamId): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.status', 's')
            ->join('r.team', 't')
            ->andWhere('s.name = :status')
            ->andWhere('t.id = :teamId')
            ->setParameter('status', $statusName)
            ->setParameter('teamId', $teamId)
            ->orderBy('r.createdDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findApprovedByEmployeeId(int $employeeId): array
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.employee', 'e')
            ->innerJoin('r.status', 's')
            ->leftJoin('r.team', 't')
            ->addSelect('e', 's', 't')
            ->andWhere('e.id = :eid')
            ->andWhere('s.name = :status')
            ->setParameter('eid', $employeeId)
            ->setParameter('status', 'APPROVED')
            ->orderBy('r.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
