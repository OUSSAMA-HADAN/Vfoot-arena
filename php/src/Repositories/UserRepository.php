<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\User;
use App\Mappers\UserMapper;
use Framework\Database\DatabaseInterface;
use Framework\Database\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    private DatabaseInterface $database;
    private UserMapper        $mapper;

    public function __construct(DatabaseInterface $database, UserMapper $mapper)
    {
        $this->database = $database;
        $this->mapper   = $mapper;
    }

    public function findByEmail(string $email): ?User
    {
        $pdo  = $this->database->getConnection();
        $stmt = $pdo->prepare('
            SELECT u.id_user AS id, u.username, u.email, u.password, u.created_at, r.nom AS role
            FROM   users u
            JOIN   role  r ON u.id_role = r.id_role
            WHERE  u.email = ?
            LIMIT  1
        ');
        $stmt->execute([$email]);
        $row  = $stmt->fetch();

        return $row !== false ? $this->mapper->map($row) : null;
    }

    public function findById(int $id): ?User
    {
        $pdo  = $this->database->getConnection();
        $stmt = $pdo->prepare('
            SELECT u.id_user AS id, u.username, u.email, u.password, u.created_at, r.nom AS role
            FROM   users u
            JOIN   role  r ON u.id_role = r.id_role
            WHERE  u.id_user = ?
            LIMIT  1
        ');
        $stmt->execute([$id]);
        $row  = $stmt->fetch();

        return $row !== false ? $this->mapper->map($row) : null;
    }

    public function create(string $email, string $username, string $hashedPassword): void
    {
        $pdo  = $this->database->getConnection();
        $stmt = $pdo->prepare('INSERT INTO users (email, username, password) VALUES (?, ?, ?)');
        $stmt->execute([$email, $username, $hashedPassword]);
    }

    public function getTrophees(int $userId): array
    {
        $pdo  = $this->database->getConnection();
        $stmt = $pdo->prepare('
            SELECT t.nom, t.icone, r.date_obtention
            FROM   remporte r
            JOIN   trophee  t ON r.id_trophee = t.id_trophee
            WHERE  r.id_user = ?
            ORDER  BY r.date_obtention DESC
        ');
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }
}
