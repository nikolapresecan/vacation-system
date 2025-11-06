<?php

namespace App\Controller;

use App\Entity\Holiday;
use App\Form\HolidayForm;
use App\Repository\HolidayRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class HolidayController extends AbstractController
{
    #[Route('/holidays/all', name: 'app_holiday_index', methods: ['GET'])]
    public function getHolidays(HolidayRepository $holidayRepository): JsonResponse
    {
        $holidays = $holidayRepository->findAll();

        $data = array_map(function($holiday) {
            return [
                'id' => $holiday->getId(),
                'name' => $holiday->getName(),
                'date' => $holiday->getDate()->format('Y-m-d'),
            ];
        }, $holidays);

        return $this->json($data);
    }

    #[Route('/admin/holidays/new', name: 'app_holiday_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $name = $data['name'] ?? null;
        $date = $data['date'] ?? null;

        if (!$name || !$date) {
            return $this->json(['error' => 'Prazno ime ili datum'], 400);
        }

        $holiday = new Holiday();
        $holiday->setName($name);
        $holiday->setDate(new \DateTime($date));

        $entityManager->persist($holiday);
        $entityManager->flush();

        return $this->json(['success' => true], 201);
    }

    /* #[Route('/{id}', name: 'app_holiday_show', methods: ['GET'])]
    public function show(Holiday $holiday): Response
    {
        return $this->render('holiday/show.html.twig', [
            'holiday' => $holiday,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_holiday_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Holiday $holiday, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HolidayForm::class, $holiday);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_holiday_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('holiday/edit.html.twig', [
            'holiday' => $holiday,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_holiday_delete', methods: ['POST'])]
    public function delete(Request $request, Holiday $holiday, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$holiday->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($holiday);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_holiday_index', [], Response::HTTP_SEE_OTHER);
    } */
}
