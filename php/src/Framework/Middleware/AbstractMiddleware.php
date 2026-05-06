<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

// Classe de base pour tous les middleware du framework.
// Fournit une ResponseFactory pour construire des redirections ou erreurs,
// et impose le contrat PSR-15 MiddlewareInterface à toutes les classes filles.
abstract class AbstractMiddleware implements MiddlewareInterface
{
    // Utilisé par les middleware enfants pour créer des réponses de redirection.
    public function __construct(protected ResponseFactoryInterface $responseFactory){}

    // Doit être implémenté par chaque middleware concret.
    // Appeler $handler->handle($request) pour passer au suivant,
    // ou retourner une réponse directement pour court-circuiter (ex: redirect /login).
    abstract public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler
    ): ResponseInterface;

    // Crée une réponse de redirection vers l'URL donnée.
    protected function redirect(string $url): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(302)
            ->withHeader('Location', $url);
    }
}
