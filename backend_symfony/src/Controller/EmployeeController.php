<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\Request as VacationRequest;
use App\Form\EmployeeForm;
use App\Repository\EmployeeRepository;
use App\Repository\RoleRepository;
use App\Repository\JobRepository;
use App\Repository\ApprovalStatusRepository;
use App\Repository\ArchiveDocumentRepository;
use App\Repository\RequestRepository;
use App\Repository\TeamEmployeeRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/employees')]
class EmployeeController extends AbstractController
{
    #[Route('/new', name: 'app_employee_new', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager, EmployeeRepository $employeeRepo, UserPasswordHasherInterface $passwordHasher, RoleRepository $roleRepository, JobRepository $jobRepository, MailerService $mailerService,  ValidatorInterface $validator): JsonResponse 
    {
        $employee = new Employee();
        
        $employee->setFirstName($request->request->get('firstName'));
        $employee->setLastName($request->request->get('lastName'));

        $oib = trim((string)$request->request->get('oib', ''));
        if (!$employeeRepo->isValidOib($oib)) {
            return new JsonResponse(['errors' => ['oib: OIB nije valjan.']], 400);
        }
        $employee->setOib($oib);

        $employee->setEmail($request->request->get('email'));
        $employee->setUsername($request->request->get('username'));

        $birthDate = \DateTime::createFromFormat('Y-m-d', $request->request->get('birthDate'));
        if ($birthDate) {
            $employee->setBirthDate($birthDate);
        }
        
        $jobId = $request->request->get('job');
        if ($jobId) {
            $job = $jobRepository->find($jobId);
            if ($job) {
                $employee->setJob($job);
            }
        }
        
        $role = $roleRepository->findOneBy(['name' => 'EMPLOYEE']);
        if ($role) {
            $employee->addRole($role);
        }

        $employee->setEmploymentDate(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Zagreb')));

        $serviceYears = (int)($request->request->get('serviceYears', 0));
        if ($serviceYears < 0) $serviceYears = 0;
        $employee->setServiceYears($serviceYears);
        $employee->setVacationDays($employeeRepo->computeVacationDays($serviceYears));
        
        $plainPassword = $request->request->get('password') ?? bin2hex(random_bytes(8));
        $hashedPassword = $passwordHasher->hashPassword($employee, $plainPassword);
        $employee->setPassword($hashedPassword);
        
        $resetToken = bin2hex(random_bytes(32));
        $expiry = new \DateTime('+1 hour', new \DateTimeZone('Europe/Zagreb'));
        $employee->setResetToken($resetToken);
        $employee->setTokenExpiry($expiry);

        $errors = $validator->validate($employee);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }
        
        $entityManager->persist($employee);
        $entityManager->flush();
        
        $frontendBaseUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:5173';
        $resetUrl = $frontendBaseUrl . '/reset-password/' . $resetToken;

        $mailerService->sendResetPasswordEmail(
            $employee->getEmail(),
            'email/reset_password.html.twig',
            [
                'resetUrl' => $resetUrl,
                'employee' => $employee,
            ]
        );

        return new JsonResponse(['message' => 'Zaposlenik uspješno dodan i email je poslan.'], 201);
    }

    #[Route('/employee/{id}', name: 'app_employee_show', methods: ['GET'])]
    public function show(Employee $employee, TeamEmployeeRepository $teamEmployeeRepository): Response
    {
        $teams = $teamEmployeeRepository->findAllByEmployee($employee);

        $teamData = [];

        foreach ($teams as $teamEmployee) {
            $teamName = $teamEmployee->getTeam()?->getName();
            $role = null;

            if ($teamEmployee->getTeamLeader()?->getId() === $employee->getId()) {
                $role = 'Team Leader';
            } elseif ($teamEmployee->getProjectManager()?->getId() === $employee->getId()) {
                $role = 'Project Manager';
            } elseif ($teamEmployee->getEmployees()->contains($employee)) {
                $role = 'Employee';
            }

            if ($teamName && $role) {
                $teamData[] = [
                    'team' => $teamName,
                    'role' => $role,
                ];
            }
        }

        return new JsonResponse([
            'id' => $employee->getId(),
            'firstName' => $employee->getFirstName(),
            'lastName' => $employee->getLastName(),
            'oib' => $employee->getOib(),
            'email' => $employee->getEmail(),
            'username' => $employee->getUsername(),
            'birthDate' => $employee->getBirthDate()?->format('Y-m-d'),
            'employmentDate' => $employee->getEmploymentDate()?->format('Y-m-d'),
            'serviceYears' => $employee->getServiceYears(),
            'vacationDays' => $employee->getVacationDays(),
            'job' => $employee->getJob() ? [
                'id' => $employee->getJob()->getId(),
                'name' => $employee->getJob()->getName(),
            ] : null,
            'roles' => $employee->getRoles(),
            'teams' => $teamData,
            'profilePicture' => $employee->getProfilePicture(),
        ]);
    }

    #[Route('/employee/{id}/edit', name: 'app_employee_edit', methods: ['PUT'])]
    public function edit(Request $request, Employee $employee, EntityManagerInterface $entityManager, JobRepository $jobRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $employee->setVacationDays($data['vacationDays']);

        if (isset($data['job'])) {
            $job = $jobRepository->find($data['job']);
            if ($job) {
                $employee->setJob($job);
            }
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Podaci zaposlenika su ažurirani.']);
    }

    #[Route('/employee/{id}/delete', name: 'app_employee_delete', methods: ['DELETE'])]
    public function delete(Request $request, Employee $employee, EntityManagerInterface $entityManager, TeamEmployeeRepository $teamEmployeeRepository): JsonResponse
    {
        if ($teamEmployeeRepository->isEmployeeAssigned($employee)) {
            return new JsonResponse(['error' => 'Zaposlenik je član tima i ne može biti obrisan.'], 400);
        }

        $entityManager->remove($employee);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Zaposlenik obrisan.']);
    }

    #[Route('/employee/{id}/approvedRequests', name: 'app_emp_approved_requests', methods: ['GET'])]
    public function getApprovedRequests(int $id, RequestRepository $requestRepo, ApprovalStatusRepository $approvalRepo, ArchiveDocumentRepository $archiveRepo, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $approvedRequests = $requestRepo->findApprovedByEmployeeId($id);

        $data = array_map(function (VacationRequest $request) use ($approvalRepo, $archiveRepo, $urlGenerator) {
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
