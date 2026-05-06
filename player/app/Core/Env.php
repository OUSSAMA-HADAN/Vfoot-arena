<?php

class Env
{
    public static function load(string $path): void
    {
        // Vérifie que le fichier .env existe
        if (!file_exists($path)) {
            throw new RuntimeException(".env introuvable : $path");
        }

        // Lit toutes les lignes du fichier
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignore les commentaires (lignes qui commencent par #)
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            // Sépare la ligne en deux : KEY et VALUE
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            $key   = trim($key);
            $value = trim($value);

            // Stocke dans $_ENV et dans putenv() pour accès global
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }

    // Récupère une variable, avec valeur par défaut si absente
    public static function get(string $key, string $default = ''): string
    {
        return $_ENV[$key] ?? $default;
    }
}
