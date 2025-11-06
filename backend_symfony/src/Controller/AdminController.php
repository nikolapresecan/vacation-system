<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Employee;
use App\Entity\Request;
use App\Entity\ApprovalStatus;
use App\Entity\ArchiveDocument;
use App\Repository\RequestRepository;
use App\Repository\TeamEmployeeRepository;
use App\Entity\TeamEmployee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/employees', name: 'admin_employees', methods: ['GET'])]
    public function employees(EntityManagerInterface $entityManager, TeamEmployeeRepository $teamEmployeeRepo): Response
    {
        $employees = $entityManager->getRepository(Employee::class)->findAll();
        $data = [];

        foreach ($employees as $employee) {
            $teamEmployees = $teamEmployeeRepo->findAllByEmployee($employee);

            $teams = [];
            foreach ($teamEmployees as $te) {
                $team = $te->getTeam();
                if ($team) {
                    $teams[] = [
                        'id' => $team->getId(),
                        'name' => $team->getName(),
                    ];
                }
            }

            $data[] = [
                'id' => $employee->getId(),
                'firstName' => $employee->getFirstName(),
                'lastName' => $employee->getLastName(),
                'birthDate' => $employee->getBirthDate()?->format('Y-m-d'),
                'roles' => $employee->getRoles(),
                'teams' => $teams,
            ];
        }

        return $this->json($data);
    }

    #[Route('/requests/approved', name: 'admin_approved_requests', methods: ['GET'])]
    public function getApprovedRequests(RequestRepository $requestRepo): JsonResponse
    {
        $createdRequests = $requestRepo->findByStatusName('APPROVED');

        $data = array_map(function (Request $request) {
            return [
                'id' => $request->getId(),
                'employee' => $request->getEmployee()->getFirstName() . ' ' . $request->getEmployee()->getLastName(),
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

    #[Route('/download/by-approval/{approvalId}', name: 'admin_archive_download_by_approval', methods: ['GET'])]
    public function downloadByApproval(int $approvalId, HttpRequest $req, EntityManagerInterface $em): BinaryFileResponse|JsonResponse
    {
        $approval = $em->getRepository(ApprovalStatus::class)->find($approvalId);
        if (!$approval) {
            return new JsonResponse(['error' => 'Odobreni zahtjev nije pronađen'], 404);
        }

        $doc = $em->getRepository(ArchiveDocument::class)
            ->findOneBy(['approvalStatus' => $approval]);

        if (!$doc) {
            return new JsonResponse(['error' => 'Rješenje ne postoji za ovaj zahtjev'], 404);
        }

        $root = (string) $this->getParameter('app_archive_dir');
        $absolute = rtrim($root, '/').'/'.$doc->getFilePath();
        if (!is_file($absolute)) {
            return new JsonResponse(['error' => 'Datoteka nije pronađena na disku'], 404);
        }

        $response = new BinaryFileResponse($absolute);
        $response->headers->set('Content-Type', 'application/pdf'); 
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            basename($absolute)
        );

        return $response;
    }
}
