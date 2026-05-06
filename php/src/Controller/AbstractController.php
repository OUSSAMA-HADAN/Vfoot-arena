<?php

declare(strict_types=1);

namespace App\Controller;

use DI\Attribute\Inject;
use Framework\Template\RendererInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

abstract class AbstractController
{
    #[Inject]
    private ResponseFactoryInterface $responseFactory;

    #[Inject]
    private StreamFactoryInterface $streamFactory;

    #[Inject]
    private RendererInterface $renderer;

    // Rendu d'un template — retourne une réponse PSR-7 200.
    public function render(string $template, array $data = []): ResponseInterface
    {
        $contents = $this->renderer->render($template, $data);
        $stream   = $this->streamFactory->createStream($contents);
        $response = $this->responseFactory->createResponse();

        return $response->withBody($stream);
    }

    // Retourne une réponse de redirection 302 vers l'URL donnée.
    // Disponible pour tous les controllers qui étendent cette classe.
    protected function redirect(string $url): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(302)
            ->withHeader('Location', $url);
    }
}
