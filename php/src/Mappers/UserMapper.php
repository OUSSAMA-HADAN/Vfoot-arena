<?php

declare(strict_types=1);

namespace App\Mappers;

use App\Entities\User;

// Transforme les lignes PDO brutes en objets User.
// Seul endroit qui connaît les noms des colonnes SQL et les setters de User.
class UserMapper
{
    // Remplit un objet User depuis une ligne PDO associative.
    public function map(array $row): User
    {
        $user = new User();

        $user->setId((int) $row['id']);
        $user->setEmail((string) $row['email']);
        $user->setPassword((string) $row['password']);
        $user->setRole((string) ($row['role'] ?? 'player'));

        return $user;
    }
}
