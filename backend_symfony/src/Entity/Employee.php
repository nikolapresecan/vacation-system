<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Ova email adresa se već koristi.')]
#[UniqueEntity(fields: ['username'], message: 'Korisničko ime je zauzeto.')]
#[UniqueEntity(fields: ['oib'], message: 'OIB već postoji u sustavu.')]
class Employee implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Ime je obavezno.')]
    private ?string $firstName = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Prezime je obavezno.')]
    private ?string $lastName = null;

    #[ORM\Column(type: 'string', length: 11, unique: true)]
    #[Assert\NotBlank(message: 'OIB je obavezan.')]
    #[Assert\Length(min: 11, max: 11, exactMessage: 'OIB mora imati točno 11 znamenki.')]
    #[Assert\Regex(pattern: '/^\d{11}$/', message: 'OIB mora sadržavati isključivo znamenke (11 znamenki).')]
    private ?string $oib = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'Datum rođenja je obavezan.')]
    #[Assert\Type(type: \DateTimeInterface::class, message: 'Datum nije ispravan.')]
    #[Assert\LessThanOrEqual('today', message: 'Datum rođenja ne može biti u budućnosti.')]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $profilePicture = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $employmentDate = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull(message: 'Godine staža moraju biti postavljene.')]
    #[Assert\Range(min: 0, max: 80, notInRangeMessage: 'Godine staža moraju biti između {{ min }} i {{ max }}.')]
    private ?int $serviceYears = 0;

    #[ORM\Column(type: 'integer')]
    private ?int $vacationDays = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Email je obavezan.')]
    #[Assert\Email(message: 'Email nije ispravan.')]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Korisničko ime je obavezno.')]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Lozinka je obavezna.')]
    private ?string $password = null;

    #[ORM\ManyToOne(targetEntity: Job::class)]
    #[ORM\JoinColumn(name: 'job_id', referencedColumnName: 'id', nullable: true)]
    private ?Job $job = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $tokenExpiry = null;

    #[ORM\ManyToMany(targetEntity: Role::class)]
    #[ORM\JoinTable(name: "employee_role")]
    private Collection $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getOib(): ?string
    {
        return $this->oib;
    }

    public function setOib(string $oib): self
    {
        $this->oib = $oib;
        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): static
    {
        $this->profilePicture = $profilePicture;
        return $this;
    }

    public function getEmploymentDate(): ?\DateTimeInterface
    {
        return $this->employmentDate;
    }

    public function setEmploymentDate(\DateTimeInterface $employmentDate): self
    {
        $this->employmentDate = $employmentDate;
        return $this;
    }

    public function getServiceYears(): ?int
    {
        return $this->serviceYears;
    }

    public function setServiceYears(int $serviceYears): self
    {
        $this->serviceYears = $serviceYears;
        return $this;
    }

    public function getVacationDays(): ?int
    {
        return $this->vacationDays;
    }

    public function setVacationDays(int $vacationDays): self
    {
        $this->vacationDays = $vacationDays;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getJob(): ?Job
    {
        return $this->job;
    }

    public function setJob(?Job $job): self
    {
        $this->job = $job;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function getTokenExpiry(): ?\DateTimeInterface
    {
        return $this->tokenExpiry;
    }

    public function setTokenExpiry(?\DateTimeInterface $tokenExpiry): self
    {
        $this->tokenExpiry = $tokenExpiry;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        return array_map(
            fn($role) => 'ROLE_' . strtoupper($role->getName()),
            $this->roles->toArray()
        );
    }

    public function getRoleEntities(): Collection
    {
        return $this->roles;
    }

    public function setRoles(iterable $roles): self
    {
        $this->roles = new ArrayCollection();
        foreach ($roles as $role) {
            $this->addRole($role);
        }
        return $this;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }

        return $this;
    }

    public function eraseCredentials(): void
    {
        
    }
}