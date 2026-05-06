<?php

declare(strict_types=1);

namespace Framework\Database;

use PDO;

// Contrat pour le gestionnaire de connexion à la base de données.
// Toute classe fournissant une connexion PDO doit implémenter cette interface.
interface DatabaseInterface
{
    // Retourne la connexion PDO active, la crée si elle n'existe pas encore.
    public function getConnection(): PDO;
}
