<?php

namespace App\Entity;

use App\Repository\ArchiveDocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ArchiveDocumentRepository::class)]
#[UniqueEntity(fields: ['documentNumber'])]
class ArchiveDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: ApprovalStatus::class)]
    #[ORM\JoinColumn(nullable: false, unique: true)]
    private ?ApprovalStatus $approvalStatus = null;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $documentNumber = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $filePath = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApprovalStatus(): ?ApprovalStatus
    {
        return $this->approvalStatus;
    }

    public function setApprovalStatus(?ApprovalStatus $approvalStatus): static
    {
        $this->approvalStatus = $approvalStatus;
        return $this;
    }

    public function getDocumentNumber(): ?string 
    { 
        return $this->documentNumber; 
    }

    public function setDocumentNumber(?string $documentNumber): static 
    { 
        $this->documentNumber = $documentNumber; 
        return $this; 
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): static
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}