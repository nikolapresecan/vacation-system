<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\Employee;
use App\Entity\TeamEmployee;
use App\Repository\TeamEmployeeRepository;
use App\Repository\EmployeeRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/teams')]
class TeamEmployeeController extends AbstractController
{
    #[Route('/team/{id}/members', name: 'api_team_members', methods: ['GET'])]
    public function getMembers(Team $team, TeamEmployeeRepository $repo): JsonResponse
    {
        $te = $repo->findOneBy(['team' => $team]);

        return $this->json([
            'teamLeader'     => $te?->getTeamLeader()?->getId(),
            'projectManager' => $te?->getProjectManager()?->getId(),
            'members'        => $te?->getEmployees()->map(fn($e) => $e->getId())->toArray() ?? [],
        ]);
    }

    #[Route('/team/{id}/add-members', name: 'app_team_add_members', methods: ['POST'])]
    public function assignMembers(Team $team, Request $request, EntityManagerInterface $em, EmployeeRepository $employeeRepo, RoleRepository $roleRepo, TeamEmployeeRepository $teamEmployeeRepo): JsonResponse 
    {
        $teamEmployee = $teamEmployeeRepo->findOneBy(['team' => $team]) ?? new TeamEmployee();
        $oldTL = $teamEmployee->getTeamLeader();
        $oldPM = $teamEmployee->getProjectManager();
        
        $teamEmployee
            ->setTeam($team)
            ->setTeamLeader(null)
            ->setProjectManager(null);
        $teamEmployee->getEmployees()->clear();
        
        $newTLId = $request->request->get('team_leader');
        $newPMId = $request->request->get('project_manager');
        $members = $request->request->all('members');

        if ($newTLId) {
            $tl = $employeeRepo->find($newTLId);
            if ($tl) {
                $teamEmployee->setTeamLeader($tl);
                $this->assignRoleReplacingDefault($tl, 'Team Leader', $roleRepo, $teamEmployeeRepo, $em);
            }
        }

        if ($newPMId && $newPMId !== $newTLId) {
            $pm = $employeeRepo->find($newPMId);
            if ($pm) {
                $teamEmployee->setProjectManager($pm);
                $this->assignRoleReplacingDefault($pm, 'Project Manager', $roleRepo, $teamEmployeeRepo, $em);
            }
        }

        foreach ($members as $memberId) {
            if ($memberId !== $newTLId && $memberId !== $newPMId) {
                $member = $employeeRepo->find($memberId);
                if ($member) {
                    $teamEmployee->getEmployees()->add($member);
                    $this->assignRoleReplacingDefault($member, 'Employee', $roleRepo, $teamEmployeeRepo, $em);
                }
            }
        }
        
        $em->persist($teamEmployee);
        $em->flush();
        
        if ($oldTL && $oldTL->getId() !== (int) $newTLId) {
            $this->cleanupRole($oldTL, 'Team Leader', $roleRepo, $teamEmployeeRepo, $em);
        }
        if ($oldPM && $oldPM->getId() !== (int) $newPMId) {
            $this->cleanupRole($oldPM, 'Project Manager', $roleRepo, $teamEmployeeRepo, $em);
        }

        return $this->json(['success' => true]);
    }
    
    private function assignRoleReplacingDefault( Employee $employee, string $newRoleName, RoleRepository $roleRepo, TeamEmployeeRepository $teamEmployeeRepo, EntityManagerInterface $em): void 
    {
        $newRole     = $roleRepo->findOneBy(['name' => $newRoleName]);
        $defaultRole = $roleRepo->findOneBy(['name' => 'Employee']);

        if (!$newRole) {
            throw new \Exception("Rola '$newRoleName' ne postoji.");
        }

        $alreadyAssigned = (bool) $teamEmployeeRepo->createQueryBuilder('te')
            ->leftJoin('te.employees', 'e')
            ->where('te.teamLeader = :emp')
            ->orWhere('te.projectManager = :emp')
            ->orWhere('e = :emp')
            ->setParameter('emp', $employee)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$alreadyAssigned && $defaultRole) {
            foreach ($employee->getRoleEntities() as $role) {
                if ($role->getName() === 'Employee') {
                    $employee->getRoleEntities()->removeElement($role);
                    break;
                }
            }
        }

        if (!$employee->getRoleEntities()->exists(fn($k, $r) => $r->getName() === $newRoleName)) {
            $employee->addRole($newRole);
        }

        $em->persist($employee);
    }
    
    private function cleanupRole(Employee $employee, string $roleName, RoleRepository $roleRepo, TeamEmployeeRepository $repo, EntityManagerInterface $em): void 
    {
        $entries = $repo->findAllByEmployee($employee);
        
        $stillHas = false;
        foreach ($entries as $entry) {
            if ($roleName === 'Team Leader' && $entry->getTeamLeader()?->getId() === $employee->getId()) {
                $stillHas = true; break;
            }
            if ($roleName === 'Project Manager' && $entry->getProjectManager()?->getId() === $employee->getId()) {
                $stillHas = true; break;
            }
        }
        
        $roleToRemove = $roleRepo->findOneBy(['name' => $roleName]);
        if (!$stillHas && $roleToRemove) {
            $employee->getRoleEntities()->removeElement($roleToRemove);
        }
        
        if (count($entries) === 0) {
            $default = $roleRepo->findOneBy(['name' => 'Employee']);
            if ($default && !$employee->getRoleEntities()->contains($default)) {
                $employee->addRole($default);
            }
        }

        $em->persist($employee);
        $em->flush();
    }


    #[Route(name: 'app_team_employee_index', methods: ['GET'])]
    public function index(TeamEmployeeRepository $teamEmployeeRepository): Response
    {
        return $this->render('team_employee/index.html.twig', [
            'team_employees' => $teamEmployeeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_team_employee_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $teamEmployee = new TeamEmployee();
        $form = $this->createForm(TeamEmployeeForm::class, $teamEmployee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($teamEmployee);
            $entityManager->flush();

            return $this->redirectToRoute('app_team_employee_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('team_employee/new.html.twig', [
            'team_employee' => $teamEmployee,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_team_employee_show', methods: ['GET'])]
    public function show(TeamEmployee $teamEmployee): Response
    {
        return $this->render('team_employee/show.html.twig', [
            'team_employee' => $teamEmployee,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_team_employee_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TeamEmployee $teamEmployee, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TeamEmployeeForm::class, $teamEmployee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_team_employee_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('team_employee/edit.html.twig', [
            'team_employee' => $teamEmployee,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_team_employee_delete', methods: ['POST'])]
    public function delete(Request $request, TeamEmployee $teamEmployee, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$teamEmployee->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($teamEmployee);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_team_employee_index', [], Response::HTTP_SEE_OTHER);
    }
}
