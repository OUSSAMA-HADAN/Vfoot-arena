<?php

use App\Middleware\AuthMiddleware;

use App\Repositories\CupRepository;
use App\Repositories\GroupRepository;
use App\Repositories\MatcheRepository;
use App\Repositories\UserRepository;

use Framework\Database\Database;
use Framework\Database\DatabaseInterface;

use Framework\Database\CupRepositoryInterface;
use Framework\Database\GroupRepositoryInterface;
use Framework\Database\MatcheRepositoryInterface;
use Framework\Database\UserRepositoryInterface;
use Framework\Template\PlatesRenderer;
use Framework\Template\RendererInterface;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

return [
    // HTTP : lie les interfaces PSR-17 à l'implémentation HttpFactory de Guzzle.
    ResponseFactoryInterface::class  => DI\create(HttpFactory::class),
    StreamFactoryInterface::class    => DI\create(HttpFactory::class),

    // Templates : PlatesRenderer encapsule le moteur League Plates.
    RendererInterface::class         => DI\create(PlatesRenderer::class),

    // Base de données : connexion PDO unique partagée, créée au premier appel.
    DatabaseInterface::class         => DI\create(Database::class),

    // Repositories : autowire résout les paramètres du constructeur automatiquement.

    // Repositories : User
    UserRepositoryInterface::class   => DI\autowire(UserRepository::class),

    // Repositories : domaine VFoot
    CupRepositoryInterface::class    => DI\autowire(CupRepository::class),
    GroupRepositoryInterface::class  => DI\autowire(GroupRepository::class),
    MatcheRepositoryInterface::class => DI\autowire(MatcheRepository::class),

    // Middleware : autowire injecte ResponseFactoryInterface via le constructeur.
    AuthMiddleware::class => DI\autowire(AuthMiddleware::class),
];