<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Middleware\AbstractMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

// Protège les routes qui nécessitent un utilisateur connecté.
// Si aucune session valide n'existe, redirige vers /login.
// Si connecté, laisse passer la requête vers le controller suivant.
class AuthMiddleware extends AbstractMiddleware
{
    public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Démarre la session si elle n'est pas encore active.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérifie qu'un utilisateur est connecté en session.
        if (empty($_SESSION['user_id'])) {
            // Pas de session — redirige vers /login et stoppe la chaîne.
            return $this->redirect('/login');
        }

        // Utilisateur connecté — passe la requête au controller suivant.
        return $handler->handle($request);
    }
}
