<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamForm;
use App\Repository\TeamRepository;
use App\Repository\TeamEmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class TeamController extends AbstractController
{
    #[Route('/admin/teams/all', name: 'app_team_index', methods: ['GET'])]
    public function index(TeamRepository $teamRepo, TeamEmployeeRepository $teamEmployeeRepo): JsonResponse
    {
        $teams = $teamRepo->findAll();

        $data = [];

        foreach ($teams as $team) {
            $teamEmployee = $teamEmployeeRepo->findOneBy(['team' => $team]);

            $data[] = [
                'id' => $team->getId(),
                'name' => $team->getName(),
                'teamLeader' => $teamEmployee && $teamEmployee->getTeamLeader()
                    ? [
                        'id' => $teamEmployee->getTeamLeader()->getId(),
                        'fullName' => $teamEmployee->getTeamLeader()->getFirstName() . ' ' . $teamEmployee->getTeamLeader()->getLastName(),
                    ]
                    : null,
                'projectManager' => $teamEmployee && $teamEmployee->getProjectManager()
                    ? [
                        'id' => $teamEmployee->getProjectManager()->getId(),
                        'fullName' => $teamEmployee->getProjectManager()->getFirstName() . ' ' . $teamEmployee->getProjectManager()->getLastName(),
                    ]
                    : null,
                'members' => $teamEmployee && $teamEmployee->getEmployees()
                    ? $teamEmployee->getEmployees()->map(function ($e) {
                        return [
                            'id' => $e->getId(),
                            'fullName' => $e->getFirstName() . ' ' . $e->getLastName(),
                        ];
                    })->toArray()
                    : [],
            ];
        }

        return $this->json($data);
    }

    #[Route('/admin/teams/new', name: 'app_team_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;

        if (!$name) {
            return $this->json(['error' => 'Naziv tima je obavezan.'], 400);
        }

        $team = new Team();
        $team->setName($name);
        $entityManager->persist($team);
        $entityManager->flush();

        return $this->json(['id' => $team->getId(), 'name' => $team->getName()], 201);
    }

    /* #[Route('/{id}', name: 'app_team_show', methods: ['GET'])]
    public function show(Team $team): Response
    {
        return $this->render('team/show.html.twig', [
            'team' => $team,
        ]);
    } */

    #[Route('/teams/{id}/membersbyrole', name: 'api_team_members_byrole', methods: ['GET'])]
    public function getTeamMembers(int $id, TeamRepository $teamRepository): JsonResponse
    {
        $team = $teamRepository->find($id);
        if (!$team) {
            return $this->json(['message' => 'Tim nije pronađen.'], 404);
        }

        $teamEmployees = $team->getTeamEmployees();

        if ($teamEmployees->isEmpty()) {
            return $this->json([]);
        }

        $members = [];
        foreach ($teamEmployees as $te) {
            if ($te->getTeamLeader()) {
                $members[] = [
                    'id' => $te->getTeamLeader()->getId(),
                    'firstName' => $te->getTeamLeader()->getFirstName(),
                    'lastName' => $te->getTeamLeader()->getLastName(),
                    'role' => 'Voditelj tima',
                ];
            }
            
            if ($te->getProjectManager()) {
                $members[] = [
                    'id' => $te->getProjectManager()->getId(),
                    'firstName' => $te->getProjectManager()->getFirstName(),
                    'lastName' => $te->getProjectManager()->getLastName(),
                    'role' => 'Voditelj projekta',
                ];
            }
            
            foreach ($te->getEmployees() as $employee) {
                $members[] = [
                    'id' => $employee->getId(),
                    'firstName' => $employee->getFirstName(),
                    'lastName' => $employee->getLastName(),
                    'role' => 'Zaposlenik',
                ];
            }
        }

        return $this->json($members);
    }

//     #[Route('/{id}/edit', name: 'app_team_edit', methods: ['GET', 'POST'])]
//     public function edit(Request $request, Team $team, EntityManagerInterface $entityManager): Response
//     {
//         $form = $this->createForm(TeamForm::class, $team);
//         $form->handleRequest($request);

//         if ($form->isSubmitted() && $form->isValid()) {
//             $entityManager->flush();

//             return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
//         }

//         return $this->render('team/edit.html.twig', [
//             'team' => $team,
//             'form' => $form,
//         ]);
//     }

//     #[Route('/{id}', name: 'app_team_delete', methods: ['POST'])]
//     public function delete(Request $request, Team $team, EntityManagerInterface $entityManager): Response
//     {
//         if ($this->isCsrfTokenValid('delete'.$team->getId(), $request->getPayload()->getString('_token'))) {
//             $entityManager->remove($team);
//             $entityManager->flush();
//         }

//         return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
//     }
}
