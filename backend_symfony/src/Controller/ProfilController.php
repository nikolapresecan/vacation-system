<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Repository\TeamEmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\MailerService;  
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/api')]
class ProfilController extends AbstractController
{
    #[Route('/profil/all', name: 'api_profile_get', methods: ['GET'])]
    public function getProfile(TeamEmployeeRepository $teamEmployeeRepository): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Niste prijavljeni.'], 401);
        }
        
        $filename = basename($user->getProfilePicture() ?? '');
        $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        
        $profilePicture = $filename
            ? $baseUrl . '/uploads/profile_pictures/' . $filename
            : $baseUrl . '/uploads/profile_pictures/default.jpg';

        $teams = $teamEmployeeRepository->findAllByEmployee($user);
        $teamData = [];

        foreach ($teams as $teamEmployee) {
            $team = $teamEmployee->getTeam();
            $role = null;

            if ($teamEmployee->getTeamLeader()?->getId() === $user->getId()) {
                $role = 'Team Leader';
            } elseif ($teamEmployee->getProjectManager()?->getId() === $user->getId()) {
                $role = 'Project Manager';
            } elseif ($teamEmployee->getEmployees()->contains($user)) {
                $role = 'Employee';
            }

            if ($team && $role) {
                $teamData[] = [
                    'id' => $team->getId(),
                    'name' => $team->getName(),
                    'role' => $role,
                ];
            }
        }
        
        $roles = $user->getRoleEntities()->map(fn($r) => $r->getName())->toArray();

        return $this->json([
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
            'birthDate' => $user->getBirthDate()?->format('Y-m-d'),
            'profilePicture' => $profilePicture,
            'vacationDays' => $user->getVacationDays(),
            'roles' => $roles,
            'teams' => $teamData,
        ]);
    }

    #[Route('/edit', name: 'api_profile_edit', methods: ['POST'])]
    public function editProfile(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Niste prijavljeni.'], 401);
        }

        $user->setFirstName($request->request->get('firstName'));
        $user->setLastName($request->request->get('lastName'));

        $birthDate = $request->request->get('birthDate');
        if ($birthDate) {
            try {
                $user->setBirthDate(new \DateTime($birthDate));
            } catch (\Exception) {
                return $this->json(['error' => 'Neispravan format datuma.'], 400);
            }
        }

        $file = $request->files->get('profilePicture');
        if ($file) {
            $filename = $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $file->guessExtension();
            $file->move($this->getParameter('profile_pictures_dir'), $filename);
            $user->setProfilePicture($filename); 
        }

        $em->flush();

        return $this->json(['message' => 'Profil uspješno ažuriran.']);
    }

    #[Route('/security/reset-password', name: 'api_reset_password_request', methods: ['POST'])]
    public function requestResetPassword(EntityManagerInterface $em, MailerService $mailerService): JsonResponse 
    {
        $user = $this->getUser();

        if (!$user instanceof Employee) {
            return $this->json(['error' => 'Niste prijavljeni.'], 401);
        }
        
        $resetToken = bin2hex(random_bytes(32));
        $expiry = new \DateTimeImmutable('+1 hour', new \DateTimeZone('Europe/Zagreb'));

        $user->setResetToken($resetToken);
        $user->setTokenExpiry($expiry);
        $em->flush();

        $frontendBaseUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:5173';
        $resetUrl = rtrim($frontendBaseUrl, '/') . '/reset-password/' . $resetToken;
        
        $mailerService->sendResetPasswordEmail(
            $user->getEmail(),
            'email/reset_password.html.twig',
            [
                'resetUrl' => $resetUrl,
                'employee' => $user,
            ]
        );

        return $this->json(['message' => 'Poveznica za reset lozinke poslana je na e-mail.']);
    }
}
