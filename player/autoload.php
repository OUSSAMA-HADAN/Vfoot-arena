<?php

// Chargement automatique des classes
// Quand PHP rencontre "new NomClasse()", cette fonction cherche
// automatiquement le fichier correspondant dans les dossiers déclarés.
// Sans ça, il faudrait écrire require_once partout.

spl_autoload_register(function (string $class): void {
    $directories = [
        __DIR__ . '/app/Core/',
        __DIR__ . '/app/Controllers/',
        __DIR__ . '/app/Models/',
        __DIR__ . '/app/Middleware/',
    ];

    foreach ($directories as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
