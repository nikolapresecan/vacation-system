<?php

namespace App\Repository;

use App\Entity\TeamEmployee;
use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamEmployee>
 */
class TeamEmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamEmployee::class);
    }

    public function isEmployeeAssigned(Employee $employee): bool
    {
        return (bool) $this->createQueryBuilder('te')
            ->leftJoin('te.employees', 'e')
            ->where('te.teamLeader = :emp')
            ->orWhere('te.projectManager = :emp')
            ->orWhere('e = :emp')
            ->setParameter('emp', $employee)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllByEmployee(Employee $employee): array
    {
        return $this->createQueryBuilder('te')
            ->leftJoin('te.employees', 'e')
            ->where('te.teamLeader = :emp')
            ->orWhere('te.projectManager = :emp')
            ->orWhere('e = :emp')
            ->setParameter('emp', $employee)
            ->getQuery()
            ->getResult();
    }
}
