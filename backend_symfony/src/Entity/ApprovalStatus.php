<?php

namespace App\Entity;

use App\Repository\ApprovalStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApprovalStatusRepository::class)]
class ApprovalStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Request::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Request $request = null;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Employee $teamLeader = null;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Employee $projectManager = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $teamLeaderApprovalDate = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $projectManagerApprovalDate = null;

    #[ORM\ManyToOne(targetEntity: Status::class)]
    private ?Status $teamLeaderStatus = null;

    #[ORM\ManyToOne(targetEntity: Status::class)]
    private ?Status $projectManagerStatus = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $teamLeaderComment = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $projectManagerComment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function setRequest(?Request $request): static
    {
        $this->request = $request;
        return $this;
    }

    public function getTeamLeader(): ?Employee
    {
        return $this->teamLeader;
    }

    public function setTeamLeader(?Employee $teamLeader): static
    {
        $this->teamLeader = $teamLeader;
        return $this;
    }

    public function getProjectManager(): ?Employee
    {
        return $this->projectManager;
    }

    public function setProjectManager(?Employee $projectManager): static
    {
        $this->projectManager = $projectManager;
        return $this;
    }

    public function getTeamLeaderApprovalDate(): ?\DateTimeInterface
    {
        return $this->teamLeaderApprovalDate;
    }

    public function setTeamLeaderApprovalDate(?\DateTimeInterface $teamLeaderApprovalDate): static
    {
        $this->teamLeaderApprovalDate = $teamLeaderApprovalDate;
        return $this;
    }

    public function getProjectManagerApprovalDate(): ?\DateTimeInterface
    {
        return $this->projectManagerApprovalDate;
    }

    public function setProjectManagerApprovalDate(?\DateTimeInterface $projectManagerApprovalDate): static
    {
        $this->projectManagerApprovalDate = $projectManagerApprovalDate;
        return $this;
    }

    public function getTeamLeaderStatus(): ?Status
    {
        return $this->teamLeaderStatus;
    }

    public function setTeamLeaderStatus(?Status $teamLeaderStatus): static
    {
        $this->teamLeaderStatus = $teamLeaderStatus;
        return $this;
    }

    public function getProjectManagerStatus(): ?Status
    {
        return $this->projectManagerStatus;
    }

    public function setProjectManagerStatus(?Status $projectManagerStatus): static
    {
        $this->projectManagerStatus = $projectManagerStatus;
        return $this;
    }

    public function getTeamLeaderComment(): ?string
    {
        return $this->teamLeaderComment;
    }

    public function setTeamLeaderComment(?string $teamLeaderComment): static
    {
        $this->teamLeaderComment = $teamLeaderComment;
        return $this;
    }

    public function getProjectManagerComment(): ?string
    {
        return $this->projectManagerComment;
    }

    public function setProjectManagerComment(?string $projectManagerComment): static
    {
        $this->projectManagerComment = $projectManagerComment;
        return $this;
    }
}
