<?php

declare(strict_types=1);

namespace App\Entities;

// Représente un utilisateur authentifié.
// Objet PHP pur — aucune connaissance de la base de données.
class User
{
    private int $id;
    private string $email;
    private string $password;
    private string $role = 'player';

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
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
}
