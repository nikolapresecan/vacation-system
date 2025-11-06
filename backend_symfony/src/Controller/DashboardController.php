<?php

namespace App\Controller;

use App\Repository\TeamEmployeeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\Security;

class DashboardController extends AbstractController
{
    #[Route('/api/admin/dashboard', name: 'api_admin_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDashboard(): JsonResponse
    {
        $user = $this->getUser();

        return $this->json([
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'photo' => $user->getProfilePicture(),
            'roles' => $user->getRoles(),
            'team' => null,
        ]);
    }

    #[Route('/api/employees/dashboard', name: 'api_employees_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_EMPLOYEE')]
    public function employeeDashboard(Request $request, TeamEmployeeRepository $repo): JsonResponse
    {
        return $this->generateDashboardResponse($request, $repo);
    }

    #[Route('/api/management/dashboard', name: 'api_management_dashboard', methods: ['GET'])]
    #[Security("is_granted('ROLE_TEAM LEADER') or is_granted('ROLE_PROJECT MANAGER')")]
    public function managementDashboard(Request $request, TeamEmployeeRepository $repo): JsonResponse
    {
        return $this->generateDashboardResponse($request, $repo);
    }

    private function generateDashboardResponse(Request $request, TeamEmployeeRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        $teamId = $request->query->get('teamId');

        if (!$teamId) {
            return $this->json(['error' => 'Parametar "teamId" je obavezan.'], 400);
        }

        $teamEmployees = $repo->findAllByEmployee($user);
        $team = null;
        $role = null;

        foreach ($teamEmployees as $te) {
            if ($te->getTeam()?->getId() == $teamId) {
                $team = $te->getTeam();

                if ($te->getTeamLeader() === $user) {
                    $role = 'ROLE_TEAM LEADER';
                } elseif ($te->getProjectManager() === $user) {
                    $role = 'ROLE_PROJECT MANAGER';
                } elseif ($te->getEmployees()->contains($user)) {
                    $role = 'ROLE_EMPLOYEE';
                }
                break;
            }
        }

        if (!$team) {
            return $this->json(['error' => 'Nemate pristup ovom timu.'], 403);
        }

        return $this->json([
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'photo' => $user->getProfilePicture(),
            'role' => $role,
            'teamId' => $team->getId(),
            'teamName' => $team->getName(),
        ]);
    }
}
