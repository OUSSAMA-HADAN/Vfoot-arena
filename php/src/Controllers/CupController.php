<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controller\AbstractController;
use DI\Attribute\Inject;
use Framework\Database\CupRepositoryInterface;
use Framework\Database\GroupRepositoryInterface;
use Framework\Database\MatcheRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CupController extends AbstractController
{
    /*
     * On aurait pu écrire les dépendances dans un constructeur :
     *
     *     public function __construct(
     *         private CupRepositoryInterface    $cupRepository,
     *         private GroupRepositoryInterface  $groupRepository,
     *         private MatcheRepositoryInterface $matcheRepository,
     *     ) {}
     *
     * #[Inject] fait exactement la même chose mais sans constructeur —
     * PHP-DI lit l'attribut via Reflection, détecte le type, et injecte
     * l'implémentation définie dans definitions.php directement dans la propriété.
     */

    #[Inject]
    private CupRepositoryInterface $cupRepository;

    #[Inject]
    private GroupRepositoryInterface $groupRepository;

    #[Inject]
    private MatcheRepositoryInterface $matcheRepository;

    public function index(): ResponseInterface
    {
        $cups = $this->cupRepository->findOpenCups();

        return $this->render('cups/index', ['cups' => $cups]);
    }

    public function show(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $id     = (int) $args['id'];
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $cup    = $this->cupRepository->findById($id);

        if ($cup === null) {
            return $this->redirect('/cups');
        }

        $groups = $this->groupRepository->findByCup($id);

        $standings = [];
        foreach ($groups as $group) {
            $standings[$group->getId()] = $this->groupRepository->getStandings($group->getId());
        }

        $isParticipant  = $this->cupRepository->isParticipant($id, $userId);
        $knockoutRounds = $this->matcheRepository->findKnockoutByCup($id);
        $participants   = $this->cupRepository->getParticipants($id);

        return $this->render('cups/show', [
            'cup'            => $cup,
            'groups'         => $groups,
            'standings'      => $standings,
            'isParticipant'  => $isParticipant,
            'knockoutRounds' => $knockoutRounds,
            'participants'   => $participants,
        ]);
    }

    public function join(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $id     = (int) $args['id'];
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $cup    = $this->cupRepository->findById($id);

        if ($cup === null || $cup->getStatut() !== 'open') {
            return $this->redirect('/cups');
        }

        if ($cup->getVisibilite() === 'private') {
            $body     = (array) $request->getParsedBody();
            $submitted = trim((string) ($body['join_code'] ?? ''));
            if ($submitted === '' || $submitted !== $cup->getJoinCode()) {
                $_SESSION['flash_error'] = 'Code d\'invitation invalide.';
                return $this->redirect('/cups/' . $id);
            }
        }

        $this->cupRepository->join($id, $userId);

        return $this->redirect('/cups/' . $id);
    }

    // Affiche le formulaire "rejoindre par code".
    public function showJoinByCode(): ResponseInterface
    {
        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);

        return $this->render('cups/join-code', ['error' => $error]);
    }

    // Traite le formulaire "rejoindre par code".
    public function joinByCode(ServerRequestInterface $request): ResponseInterface
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $body   = (array) $request->getParsedBody();
        $code   = trim((string) ($body['join_code'] ?? ''));

        if ($code === '') {
            $_SESSION['flash_error'] = 'Veuillez entrer un code.';
            return $this->redirect('/cups/join-code');
        }

        $cup = $this->cupRepository->findByJoinCode($code);

        if ($cup === null) {
            $_SESSION['flash_error'] = 'Code invalide — aucune cup trouvée.';
            return $this->redirect('/cups/join-code');
        }

        if ($cup->getStatut() !== 'open') {
            $_SESSION['flash_error'] = 'Cette cup n\'accepte plus de participants.';
            return $this->redirect('/cups/join-code');
        }

        if (!$this->cupRepository->isParticipant($cup->getId(), $userId)) {
            $this->cupRepository->join($cup->getId(), $userId);
        }

        return $this->redirect('/cups/' . $cup->getId());
    }
}
