<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\CupController;
use App\Controllers\HomeController;
use App\Controllers\MatcheController;
use App\Controllers\ProfileController;

use App\Middleware\AuthMiddleware;
use League\Route\RouteGroup;
use League\Route\Router;
use Psr\Container\ContainerInterface;

// Toutes les routes de l'application sont définies ici.
// Le $container est passé pour que les middleware soient résolus via PHP-DI.
return function (Router $router, ContainerInterface $container) {

    $authMiddleware = $container->get(AuthMiddleware::class);

    // -------------------------------------------------------
    // Routes publiques — aucune authentification requise
    // -------------------------------------------------------
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/login',    [AuthController::class, 'showLogin']);
    $router->post('/login',   [AuthController::class, 'login']);
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register',[AuthController::class, 'register']);
    $router->get('/logout',   [AuthController::class, 'logout']);

    // -------------------------------------------------------
    // Routes protégées — AuthMiddleware vérifie la session
    // -------------------------------------------------------
    $router->group('', function (RouteGroup $router) {

        // Cups
        $router->get('/cups',                      [CupController::class, 'index']);
        $router->get('/cups/{id:number}',          [CupController::class, 'show']);
        $router->post('/cups/{id:number}/join',    [CupController::class, 'join']);

        // Matchs
        $router->get('/matches',                       [MatcheController::class, 'index']);
        $router->get('/matches/{id:number}',           [MatcheController::class, 'show']);
        $router->post('/matches/{id:number}/score',    [MatcheController::class, 'submitScore']);

        // Profil
        $router->get('/profile', [ProfileController::class, 'show']);

    })->middleware($authMiddleware);
};
