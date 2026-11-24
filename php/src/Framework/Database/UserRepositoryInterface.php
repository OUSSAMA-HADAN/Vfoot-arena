<?php

declare(strict_types=1);

namespace Framework\Database;

use App\Entities\User;

// Contrat pour toutes les opérations d'accès aux données utilisateur.
interface UserRepositoryInterface extends RepositoryInterface
{
    // Trouve un utilisateur par son email — utilisé lors de la connexion.
    public function findByEmail(string $email): ?User;

    // Trouve un utilisateur par son ID — utilisé pour recharger la session.
    public function findById(int $id): ?User;

    // Crée un nouvel utilisateur avec email, username et mot de passe hashé bcrypt.
    public function create(string $email, string $username, string $hashedPassword): void;

    // Retourne les trophées remportés par un joueur : [nom, icone, date_obtention][].
    public function getTrophees(int $userId): array;
}
