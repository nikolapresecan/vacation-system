<?php

namespace App\Repository;

use App\Entity\ApprovalStatus;
use App\Entity\Request;
use App\Entity\Employee;
use App\Entity\Status;
use App\Repository\TeamEmployeeRepository;
use App\Repository\StatusRepository;
use App\Service\MailerService;
use App\Service\ArchiveDocumentCreator;
use App\Service\VacationApprovalService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApprovalStatus>
 */
class ApprovalStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private VacationApprovalService $service)
    {
        
        parent::__construct($registry, ApprovalStatus::class);
        $this->service = $service;
    }
    
    public function processApproval(Request $request, Employee $user, Status $status, ?string $comment, TeamEmployeeRepository $teamEmployeeRepo, StatusRepository $statusRepo, MailerService $mailerService, ArchiveDocumentCreator $docCreator): void 
    {
        $em = $this->getEntityManager();
        $now = new \DateTime();

        $approvalStatus = $this->findOneBy(['request' => $request]);
        if (!$approvalStatus) {
            $approvalStatus = new ApprovalStatus();
            $approvalStatus->setRequest($request);
            $em->persist($approvalStatus);
        }

        $team = $request->getTeam();
        $teamEmployee = $teamEmployeeRepo->findOneBy(['team' => $team]);

        if (!$teamEmployee) {
            throw new \RuntimeException('Voditelji za tim nisu definirani.');
        }

        if ($teamEmployee->getTeamLeader() && $user->getId() === $teamEmployee->getTeamLeader()->getId()) {
            if ($approvalStatus->getTeamLeaderStatus() !== null) {
                throw new \RuntimeException('Voditelj tima je već donio odluku.');
            }
            $approvalStatus->setTeamLeader($user);
            $approvalStatus->setTeamLeaderStatus($status);
            $approvalStatus->setTeamLeaderApprovalDate($now);
            $approvalStatus->setTeamLeaderComment($comment);
        }

        if ($teamEmployee->getProjectManager() && $user->getId() === $teamEmployee->getProjectManager()->getId()) {
            if ($approvalStatus->getProjectManagerStatus() !== null) {
                throw new \RuntimeException('Voditelj projekta je već donio odluku.');
            }
            $approvalStatus->setProjectManager($user);
            $approvalStatus->setProjectManagerStatus($status);
            $approvalStatus->setProjectManagerApprovalDate($now);
            $approvalStatus->setProjectManagerComment($comment);
        }

        $statusApproved = $statusRepo->findOneBy(['name' => 'APPROVED']);
        $statusDeclined = $statusRepo->findOneBy(['name' => 'DECLINED']);

        if (
            $approvalStatus->getTeamLeaderStatus()?->getName() === 'APPROVED' &&
            $approvalStatus->getProjectManagerStatus()?->getName() === 'APPROVED'
        ) {

            $employee = $request->getEmployee();
            $employee->setVacationDays($employee->getVacationDays() - $request->getNumberOfDays());
            $request->setStatus($statusApproved);

            $em->persist($employee);
            $em->persist($request);
            $em->flush();

            $archiveDoc = $docCreator->createFromApproval($approvalStatus);
            $pdfBytes = $docCreator->readAbsolute($archiveDoc);

            $mailerService->sendVacationApprovalEmail(
                $request,
                $pdfBytes,
                $archiveDoc->getDocumentNumber(),
                basename($archiveDoc->getFilePath()),
                $approvalStatus->getTeamLeaderComment(),
                $approvalStatus->getProjectManagerComment()
            );
        } elseif (
            $approvalStatus->getTeamLeaderStatus()?->getName() === 'DECLINED' ||
            $approvalStatus->getProjectManagerStatus()?->getName() === 'DECLINED'
        ) {
            $employee = $request->getEmployee();
            $request->setStatus($statusDeclined);

            $mailerService->sendVacationDeclineEmail(
                $request,
                $approvalStatus->getTeamLeaderComment(),
                $approvalStatus->getProjectManagerComment()
            );
        }

        $em->persist($request);
        $em->flush();
    }
}
