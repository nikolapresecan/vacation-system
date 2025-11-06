<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class PasswordResetController extends AbstractController
{
    #[Route('/reset-password/{token}', name: 'app_reset_password', methods: ['POST'])]
    public function resetPassword(string $token, Request $request, EmployeeRepository $employeeRepository, UserPasswordHasherInterface $passwordHasher): JsonResponse 
    {
        $employee = $employeeRepository->findOneBy(['resetToken' => $token]);

        if (!$employee || $employee->getTokenExpiry() < new \DateTime()) {
            return new JsonResponse(['error' => 'Token je nevažeći ili je istekao.'], 400);
        }

        $plainPassword = $request->request->get('password');
        if (!$plainPassword) {
            return new JsonResponse(['error' => 'Lozinka je obavezna.'], 400);
        }

        $hashedPassword = $passwordHasher->hashPassword($employee, $plainPassword);
        
        $employeeRepository->resetPassword($employee, $hashedPassword);

        return new JsonResponse(['message' => 'Lozinka je uspješno postavljena.']);
    }
}