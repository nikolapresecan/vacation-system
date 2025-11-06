<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employee>
 */
class EmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    public function resetPassword(Employee $employee, string $hashedPassword): void
    {
        $employee->setPassword($hashedPassword);
        $employee->setResetToken(null);
        $employee->setTokenExpiry(null);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($employee);
        $entityManager->flush();
    }

     public function isValidOib(string $oib): bool
    {
        if (!preg_match('/^\d{11}$/', $oib)) {
            return false;
        }

        $a = 10;

        for ($i = 0; $i < 10; $i++) {
            $a = ($a + (int)$oib[$i]) % 10;
            if ($a === 0) $a = 10;
            $a = ($a * 2) % 11;
        }

        $control = 11 - $a;
        if ($control === 10) $control = 0;
        
        return $control === (int)$oib[10];
    }

    public function computeVacationDays(int $years): int
    {
        if ($years >= 15) return 30;
        if ($years >= 10) return 25;
        if ($years >= 5)  return 23;
        return 20;
    }
}
