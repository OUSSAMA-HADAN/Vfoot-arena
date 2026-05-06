<?php

declare(strict_types=1);

namespace Framework\Database;

use PDO;

// Gère une seule connexion PDO partagée pour toute la durée de la requête.
// Lit les identifiants depuis $_ENV (chargé via .env et phpdotenv).
class Database implements DatabaseInterface
{
    // La connexion une fois créée. Null jusqu'au premier appel à getConnection().
    private ?PDO $connection = null;

    // Retourne la connexion PDO, la crée au premier appel (initialisation lazy).
    // Réutilise la même instance ensuite — pas de reconnexion à chaque requête.
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $_ENV['DB_HOST'],
                $_ENV['DB_NAME']
            );

            $this->connection = new PDO(
                $dsn,
                $_ENV['DB_USERNAME'],
                $_ENV['DB_PASSWORD'],
                [
                    // Lève des exceptions en cas d'erreur DB.
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    // Retourne les lignes sous forme de tableaux associatifs.
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        }

        return $this->connection;
    }
}
