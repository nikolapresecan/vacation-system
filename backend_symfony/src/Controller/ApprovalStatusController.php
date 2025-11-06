<?php

namespace App\Controller;

use App\Entity\ApprovalStatus;
use App\Entity\Request;
use App\Entity\Employee;
use App\Form\ApprovalStatusForm;
use App\Repository\ApprovalStatusRepository;
use App\Repository\ArchiveDocumentRepository;
use App\Repository\RequestRepository;
use App\Repository\StatusRepository;
use App\Repository\TeamEmployeeRepository;
use App\Service\MailerService;
use App\Service\ArchiveDocumentCreator;
use App\Service\VacationApprovalService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as HTTPRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/api/management')]
class ApprovalStatusController extends AbstractController
{
    #[Route('/approve', name: 'app_approve', methods: ['POST'])]
    public function new(HTTPRequest $request, EntityManagerInterface $em, RequestRepository $requestRepo, StatusRepository $statusRepo, TeamEmployeeRepository $teamEmployeeRepo, ApprovalStatusRepository $approvalRepo, MailerService $mailer, ArchiveDocumentCreator $docCreator): JsonResponse 
    {
        $data = json_decode($request->getContent(), true);

        $requestId = $data['id'] ?? null;
        $statusName = $data['status'] ?? null;
        $comment = $data['comment'] ?? null;

        if (!$requestId || !is_numeric($requestId)) {
            return new JsonResponse(['error' => 'ID zahtjeva nije valjan.'], 400);
        }

        $vacationRequest = $requestRepo->find($requestId);
        if (!$vacationRequest) {
            return new JsonResponse(['error' => 'Zahtjev nije pronađen.'], 404);
        }
        
        $user = $this->getUser();

        $status = $statusRepo->findOneBy(['name' => $statusName]);
        if (!$status) {
            return new JsonResponse(['error' => "Status '$statusName' ne postoji."], 400);
        }

        try {
            $approvalRepo->processApproval(
                $vacationRequest,
                $user,
                $status,
                $comment,
                $teamEmployeeRepo,
                $statusRepo,
                $mailer,
                $docCreator
            );
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Neočekivana greška.'], 500);
        } 

        return new JsonResponse(['message' => 'Status odobravanja je uspješno spremljen.']);
    }

    #[Route('/requests/created', name: 'app_created_requests', methods: ['GET'])]
    public function getCreatedRequests(HttpRequest $httpRequest, RequestRepository $requestRepo, VacationApprovalService $priority): JsonResponse
    {
        $teamId = $httpRequest->query->getInt('teamId', 0);

        if ($teamId > 0) {
            $createdRequests = $requestRepo->findByStatusNameAndTeamId('CREATED', $teamId);
        } else {
            $createdRequests = $requestRepo->findByStatusName('CREATED');
        }

        $queue = $priority->buildPriorityQueue($createdRequests);

        $ordered = [];
        while (!$queue->isEmpty()) {
            $r = $queue->extract();
            $ordered[] = $r;
        }

        $data = array_map(function (Request $request) use ($priority) {
            return [
                'id' => $request->getId(),
                'employee' => $request->getEmployee()->getFirstName().' '.$request->getEmployee()->getLastName(),
                'startDate' => $request->getStartDate()->format('Y-m-d'),
                'endDate' => $request->getEndDate()->format('Y-m-d'),
                'numberOfDays' => $request->getNumberOfDays(),
                'team' => $request->getTeam()->getName(),
                'teamId' => $request->getTeam()->getId(),
                'comment' => $request->getComment(),
                'createdDate' => $request->getCreatedDate()->format('Y-m-d H:i'),
            ];
        }, $createdRequests);

        return new JsonResponse($data);
    }

    #[Route('/requests/approved', name: 'app_approved_requests', methods: ['GET'])]
    public function getApprovedRequests(RequestRepository $requestRepo, ApprovalStatusRepository $approvalRepo, ArchiveDocumentRepository $archiveRepo, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $approvedRequests = $requestRepo->findByStatusName('APPROVED');

        $data = array_map(function (Request $request) use ($approvalRepo, $archiveRepo, $urlGenerator) {
            $approval   = $approvalRepo->findOneBy(['request' => $request]);
            $approvalId = $approval?->getId();

            $doc       = $approval ? $archiveRepo->findOneBy(['approvalStatus' => $approval]) : null;
            $filePath  = $doc?->getFilePath();
            $solutionUrl = ($approvalId && $filePath)
                ? $urlGenerator->generate(
                    'archive_download_by_approval',         
                    ['approvalId' => $approvalId],
                    UrlGeneratorInterface::ABSOLUTE_URL
                  ) . '?inline=1'
                : null;

            return [
                'id'               => $request->getId(),
                'approvalStatusId' => $approvalId,
                'employee'         => $request->getEmployee()->getFirstName() . ' ' . $request->getEmployee()->getLastName(),
                'startDate'        => $request->getStartDate()->format('Y-m-d'),
                'endDate'          => $request->getEndDate()->format('Y-m-d'),
                'numberOfDays'     => $request->getNumberOfDays(),
                'team'             => $request->getTeam()->getName(),
                'teamId'           => $request->getTeam()->getId(),
                'comment'          => $request->getComment(),
                'createdDate'      => $request->getCreatedDate()->format('Y-m-d H:i'),
                'filePath'         => $filePath,    
                'solutionUrl'      => $solutionUrl,
            ];
        }, $approvedRequests);

        return new JsonResponse($data);
    }
}
