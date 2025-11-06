<?php

namespace App\Entity;

use App\Repository\TeamEmployeeRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: TeamEmployeeRepository::class)]
class TeamEmployee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'teamEmployees')]
     #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true)]
    private ?Team $team = null;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    private ?Employee $teamLeader = null;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    private ?Employee $projectManager = null;

    #[ORM\ManyToMany(targetEntity: Employee::class)]
    #[ORM\JoinTable(name: "team_employee_members")]
    private Collection $employees;

    public function __construct()
    {
        $this->employees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeam(): ?Team 
    { 
        return $this->team; 
    }

    public function setTeam(?Team $team): static 
    {
        $this->team = $team; return $this; 
    }

    public function getTeamLeader(): ?Employee 
    { 
        return $this->teamLeader; 
    }

    public function setTeamLeader(?Employee $employee): static 
    { 
        $this->teamLeader = $employee; return $this; 
    }

    public function getProjectManager(): ?Employee 
    { 
        return $this->projectManager; 
    }
    
    public function setProjectManager(?Employee $employee): static 
    { 
        $this->projectManager = $employee; return $this; 
    }

    public function getEmployees(): Collection 
    { 
        return $this->employees; 
    }
}
