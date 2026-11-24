<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controller\AbstractController;
use DI\Attribute\Inject;
use Framework\Database\CupRepositoryInterface;
use Framework\Database\MatcheRepositoryInterface;
use Framework\Database\UserRepositoryInterface;
use Psr\Http\Message\ResponseInterface;

class ProfileController extends AbstractController
{
    #[Inject]
    private UserRepositoryInterface $userRepository;

    #[Inject]
    private MatcheRepositoryInterface $matcheRepository;

    // Affiche le profil du joueur connecté : ses infos, ses matchs récents et le nombre de trophées.
    public function show(): ResponseInterface
    {
        $userId   = (int) ($_SESSION['user_id'] ?? 0);
        $user     = $this->userRepository->findById($userId);
        $matches  = $this->matcheRepository->findByUser($userId);
        $trophees = $this->userRepository->getTrophees($userId);

        return $this->render('profile/show', [
            'user'          => $user,
            'matches'       => $matches,
            'tropheeCount'  => count($trophees),
        ]);
    }

    // Affiche la page complète des trophées du joueur connecté.
    public function trophees(): ResponseInterface
    {
        $userId   = (int) ($_SESSION['user_id'] ?? 0);
        $user     = $this->userRepository->findById($userId);
        $trophees = $this->userRepository->getTrophees($userId);

        return $this->render('profile/trophees', [
            'user'     => $user,
            'trophees' => $trophees,
        ]);
    }
}
