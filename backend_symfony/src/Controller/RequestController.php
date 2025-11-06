<?php

namespace App\Controller;

use App\Entity\Request;
use App\Entity\Team;
use App\Repository\RequestRepository;
use App\Repository\HolidayRepository;
use App\Repository\StatusRepository;
use App\Repository\ApprovalStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/employees/requests')]
final class RequestController extends AbstractController
{
    #[Route('/team/{teamId}/new', name: 'app_request_new', methods: ['POST'])]
    public function new(HttpRequest $httpRequest, int $teamId, EntityManagerInterface $entityManager, HolidayRepository $holidayRepo, StatusRepository $statusRepo): JsonResponse 
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Niste prijavljeni.'], 401);
        }

        $team = $entityManager->getRepository(Team::class)->find($teamId);
        if (!$team) {
            return $this->json(['error' => 'Tim nije pronađen.'], 404);
        }

        $data = json_decode($httpRequest->getContent(), true);
        if (!$data || !isset($data['startDate'], $data['endDate'], $data['comment'])) {
            return $this->json(['error' => 'Nedostaju potrebna polja.'], 400);
        }

        try {
            $startDate = new \DateTime($data['startDate']);
            $endDate = new \DateTime($data['endDate']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Neispravan format datuma.'], 400);
        }

        if ($endDate < $startDate) {
            return $this->json(['error' => 'Datum završetka ne može biti prije početka.'], 400);
        }

        $holidays = array_map(
            fn($holiday) => $holiday->getDate()->format('Y-m-d'),
            $holidayRepo->findAll()
        );

        $workingDays = 0;
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), (clone $endDate)->modify('+1 day'));
        foreach ($period as $date) {
            $day = $date->format('Y-m-d');
            if (!in_array($date->format('N'), ['6', '7']) && !in_array($day, $holidays)) {
                $workingDays++;
            }
        }

        if ($workingDays > $user->getVacationDays()) {
            return $this->json([
                'error' => "Nemate dovoljno dana godišnjeg odmora. Odabrano: $workingDays, Dostupno: " . $user->getVacationDays()
            ], 400);
        }

        $status = $statusRepo->findOneBy(['name' => 'CREATED']);
        if (!$status) {
            return $this->json(['error' => 'Status "CREATED" nije pronađen u bazi.'], 500);
        }

        $request = new Request();
        $request
            ->setEmployee($user)
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setComment($data['comment'])
            ->setCreatedDate(new \DateTime())
            ->setNumberOfDays($workingDays)
            ->setStatus($status)
            ->setTeam($team);

        $entityManager->persist($request);
        $entityManager->flush();

        return $this->json([
            'message' => 'Zahtjev za godišnji odmor je uspješno poslan.',
            'workingDays' => $workingDays,
            'teamId' => $team->getId()
        ], 201);
    }

    #[Route('/myrequests', name: 'app_request_all', methods: ['GET'])]
    public function getUserRequests(RequestRepository $requestRepository): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Niste prijavljeni.'], 401);
        }

        $requests = $requestRepository->findBy(['employee' => $user], ['createdDate' => 'DESC']);

        $grouped = [];
        foreach ($requests as $request) {
            $team = $request->getTeam();
            $teamId = $team?->getId();
            $teamName = $team?->getName() ?? 'Nepoznat tim';

            if (!isset($grouped[$teamId])) {
                $grouped[$teamId] = [
                    'teamId' => $teamId,
                    'teamName' => $teamName,
                    'requests' => []
                ];
            }

            $grouped[$teamId]['requests'][] = [
                'id' => $request->getId(),
                'startDate' => $request->getStartDate()?->format('Y-m-d'),
                'endDate' => $request->getEndDate()?->format('Y-m-d'),
                'numberOfDays' => $request->getNumberOfDays(),
                'comment' => $request->getComment(),
                'statusId' => $request->getStatus()?->getId(),
                'createdDate' => $request->getCreatedDate()?->format('Y-m-d H:i'),
            ];
        }

        return $this->json(array_values($grouped));
    }

    #[Route('/{id}/approval-status', name: 'app_request_approval_status', methods: ['GET'])]
    public function getApprovalStatusForRequest(int $id, ApprovalStatusRepository $approvalRepo, RequestRepository $requestRepo): JsonResponse 
    {
       $request = $requestRepo->find($id);

        if (!$request) {
            return new JsonResponse(['error' => 'Zahtjev nije pronađen.'], 404);
        }

        $approval = $approvalRepo->findOneBy(['request' => $request]);

        if (!$approval) {
            return new JsonResponse([]); 
        }

        $data = [];
        
        if ($approval->getTeamLeader()) {
            $data[] = [
                'role' => 'Voditelj tima',
                'approver' => $approval->getTeamLeader()->getFirstName() . ' ' . $approval->getTeamLeader()->getLastName(),
                'status' => $approval->getTeamLeaderStatus()?->getName(),
                'comment' => $approval->getTeamLeaderComment(),
                'approvedAt' => $approval->getTeamLeaderApprovalDate()?->format('Y-m-d H:i'),
            ];
        }
        
        if ($approval->getProjectManager()) {
            $data[] = [
                'role' => 'Voditelj projekta',
                'approver' => $approval->getProjectManager()->getFirstName() . ' ' . $approval->getProjectManager()->getLastName(),
                'status' => $approval->getProjectManagerStatus()?->getName(),
                'comment' => $approval->getProjectManagerComment(),
                'approvedAt' => $approval->getProjectManagerApprovalDate()?->format('Y-m-d H:i'),
            ];
        }

        return new JsonResponse($data);
    }


    /* #[Route('/{id}', name: 'app_request_show', methods: ['GET'])]
    public function show(Request $request): Response
    {
        return $this->render('request/show.html.twig', [
            'request' => $request,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_request_edit', methods: ['GET', 'POST'])]
    public function edit(HttpRequest $httprequest, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RequestForm::class, $request);
        $form->handleRequest($httprequest);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_request_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('request/edit.html.twig', [
            'request' => $request,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_request_delete', methods: ['POST'])]
    public function delete(HttpRequest $httprequest, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$request->getId(), $httprequest->getPayload()->getString('_token'))) {
            $entityManager->remove($request);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_request_index', [], Response::HTTP_SEE_OTHER);
    } */
}
