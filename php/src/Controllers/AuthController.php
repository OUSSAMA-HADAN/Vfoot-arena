<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controller\AbstractController;
use DI\Attribute\Inject;
use Framework\Database\UserRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// Gère la connexion, l'inscription et la déconnexion.
// Pas de middleware auth sur ces routes — elles restent publiques.
class AuthController extends AbstractController
{
    // Injecté par PHP-DI — résolu vers UserRepository via definitions.php.
    #[Inject]
    private UserRepositoryInterface $userRepository;

    // Affiche le formulaire de connexion.
    public function showLogin(): ResponseInterface
    {
        return $this->render('auth/login', []);
    }

    // Traite la soumission du formulaire de connexion.
    // Vérifie les identifiants, ouvre la session et redirige.
    public function login(ServerRequestInterface $request): ResponseInterface
    {
        $body     = $request->getParsedBody();
        $email    = trim((string) ($body['email'] ?? ''));
        $password = (string) ($body['password'] ?? '');

        $user = $this->userRepository->findByEmail($email);

        // Vérifie le mot de passe soumis contre le hash bcrypt stocké.
        if ($user === null || !password_verify($password, $user->getPassword())) {
            return $this->render('auth/login', [
                'error' => 'Invalid email or password.',
            ]);
        }

        $_SESSION['user_id']   = $user->getId();
        $_SESSION['user_role'] = $user->getRole();

        return $this->redirect('/cups');
    }

    // Affiche le formulaire d'inscription.
    public function showRegister(): ResponseInterface
    {
        return $this->render('auth/register', []);
    }

    // Traite la soumission du formulaire d'inscription.
    // Hash le mot de passe, crée le compte et redirige vers la connexion.
    public function register(ServerRequestInterface $request): ResponseInterface
    {
        $body     = $request->getParsedBody();
        $email    = trim((string) ($body['email']    ?? ''));
        $username = trim((string) ($body['username'] ?? ''));
        $password = (string) ($body['password']      ?? '');
        $confirm  = (string) ($body['confirm']       ?? '');

        if (empty($email) || empty($username) || empty($password)) {
            return $this->render('auth/register', [
                'error' => 'Tous les champs sont obligatoires.',
            ]);
        }

        if ($password !== $confirm) {
            return $this->render('auth/register', [
                'error' => 'Les mots de passe ne correspondent pas.',
            ]);
        }

        if ($this->userRepository->findByEmail($email) !== null) {
            return $this->render('auth/register', [
                'error' => 'Un compte existe déjà avec cet email.',
            ]);
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->userRepository->create($email, $username, $hash);

        return $this->redirect('/login');
    }

    // Détruit la session et redirige vers la page de connexion.
    public function logout(): ResponseInterface
    {
        $_SESSION = [];
        session_destroy();

        return $this->redirect('/login');
    }
}
