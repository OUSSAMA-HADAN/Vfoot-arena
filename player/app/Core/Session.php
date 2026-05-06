<?php

class Session
{
    // Démarre la session si elle n'est pas encore démarrée
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Lire une valeur de session
    public static function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    // Écrire une valeur en session
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    // Supprimer une clé de session
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    // Détruire toute la session (déconnexion)
    public static function destroy(): void
    {
        session_destroy();
        $_SESSION = [];
    }

    // Message flash : stocké en session, lu une seule fois puis supprimé
    // Utilisé pour afficher "Connexion réussie !" après une redirection
    public static function flash(string $key, string $message): void
    {
        $_SESSION['_flash'][$key] = $message;
    }

    public static function getFlash(string $key): ?string
    {
        $message = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $message;
    }
}
