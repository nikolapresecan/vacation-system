<?php

namespace App\EventListener;

use App\Entity\Employee;
use App\Repository\TeamEmployeeRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'lexik_jwt_authentication.on_jwt_created')]
final class JWTCreatedListener
{
    public function __construct(private TeamEmployeeRepository $teamEmployeeRepository) {}

    public function __invoke(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();

        $teamRoles = [];
        $teamEmployees = $this->teamEmployeeRepository->findAllByEmployee($user);

        foreach ($teamEmployees as $te) {
            $team = $te->getTeam();
            if (!$team) continue;

            if ($te->getTeamLeader() === $user) {
                $teamRoles[] = [
                    'teamId' => $team->getId(),
                    'teamName' => $team->getName(),
                    'role' => 'ROLE_TEAM LEADER',
                ];
            }

            if ($te->getProjectManager() === $user) {
                $teamRoles[] = [
                    'teamId' => $team->getId(),
                    'teamName' => $team->getName(),
                    'role' => 'ROLE_PROJECT MANAGER',
                ];
            }

            if ($te->getEmployees()->contains($user)) {
                $teamRoles[] = [
                    'teamId' => $team->getId(),
                    'teamName' => $team->getName(),
                    'role' => 'ROLE_EMPLOYEE',
                ];
            }
        }

        $payload = $event->getData();
        $payload['teamRoles'] = $teamRoles;
        $event->setData($payload);
    }
}
