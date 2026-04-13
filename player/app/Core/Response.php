<?php

class Response
{
    // Redirige vers une autre URL et arrête l'exécution
    public static function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    // Envoie une réponse JSON (pour les requêtes AJAX)
    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Envoie un code HTTP sans contenu (ex: 404, 403...)
    public static function abort(int $status, string $message = ''): never
    {
        http_response_code($status);
        echo $message;
        exit;
    }
}
