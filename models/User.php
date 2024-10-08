<?php

class User
{
    private ?int $id = null;

    public function __construct(
        private string $firstName,
        private string $lastName,
        private string $email,
        private string $password,
        private string $role = "USER",
        private ?Specialization $specialization = null,
        private ?Media $media = null,
        private ?DateTime $createdAt = null,
        private ?string $structure = null
    ) {
        $this->createdAt = $this->createdAt ?? new DateTime('now', new DateTimeZone('Europe/Paris'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getSpecialization(): ?Specialization
    {
        return $this->specialization;
    }


    public function getSpecializationId(): ?int
    {
        return $this->specialization?->getId();
    }

    public function setSpecialization(?Specialization $specialization): void
    {
        $this->specialization = $specialization;
    }

    public function getStructure(): ?string
    {
        return $this->structure;
    }

    public function setStructure(?string $structure): void
    {
        $this->structure = $structure;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): void
    {
        $this->media = $media;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
