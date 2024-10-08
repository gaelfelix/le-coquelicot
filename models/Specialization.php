<?php

class Specialization
{

    private ?int $id;

    public function __construct(
        private string $name,
        private string $role
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole($role): void
    {
        $this->role = $role;
    }
}