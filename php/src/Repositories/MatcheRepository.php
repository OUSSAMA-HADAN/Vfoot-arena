<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Mappers\MatcheMapper;
use Framework\Database\MatcheRepositoryInterface;
use Framework\Database\DatabaseInterface;

class MatcheRepository implements MatcheRepositoryInterface
{
    private DatabaseInterface $database;
    private MatcheMapper      $mapper;

    public function __construct(DatabaseInterface $database, MatcheMapper $mapper)
    {
        $this->database = $database;
        $this->mapper   = $mapper;
    }

    // ----------------------------------------------------------------
    // Requête de base partagée par toutes les méthodes de lecture.
    // On fait les jointures une seule fois ici plutôt que de les
    // répéter dans chaque méthode — on ajoute juste le WHERE après.
    //
    // LEFT JOIN sur resultat : la plupart des matchs n'ont pas encore
    // de résultat, LEFT JOIN évite de les perdre (contrairement à JOIN).

    private function baseQuery(): string
    {
        return "
            SELECT m.id_match, m.round, m.leg, m.statut,
                   h.username  AS home_name,  a.username  AS away_name,
                   m.home_user_id,             m.away_user_id,
                   c.titre     AS cup_titre,
                   r.home_score AS result_home, r.away_score AS result_away
            FROM      matche   m
            JOIN      users    h ON m.home_user_id = h.id_user
            JOIN      users    a ON m.away_user_id = a.id_user
            JOIN      cup      c ON m.id_cup       = c.id_cup
            LEFT JOIN resultat r ON r.id_match     = m.id_match
        ";
    }

    // ----------------------------------------------------------------
    // Tous les matchs d'un joueur (page profil).
    // FIELD() permet de trier par statut dans un ordre logique :
    // d'abord ce qui demande une action (pending, disputed),
    // ensuite ce qui est terminé (confirmed).

    public function findByUser(int $idUser): array
    {
        $pdo  = $this->database->getConnection();
        $stmt = $pdo->prepare(
            $this->baseQuery() . "
            WHERE (m.home_user_id = ? OR m.away_user_id = ?)
            ORDER BY FIELD(m.statut, 'pending','submitted','disputed','confirmed','forfeited'),
                     m.created_at DESC
            "
        );
        $stmt->execute([$idUser, $idUser]);

        return $this->mapper->mapToList($stmt->fetchAll());
    }

    // ----------------------------------------------------------------
    // Matchs éliminatoires d'une cup (bracket).
    // On exclut les matchs de groupe (round != 'group') et on regroupe
    // les résultats par round pour que la vue puisse afficher
    // QF → SF → Finale dans le bon ordre.

    public function findKnockoutByCup(int $idCup): array
    {
        $pdo  = $this->database->getConnection();
        $stmt = $pdo->prepare(
            $this->baseQuery() . "
            WHERE m.round != 'group' AND m.id_cup = ?
            ORDER BY FIELD(m.round,'R16','QF','SF','F'), m.leg, m.id_match
            "
        );
        $stmt->execute([$idCup]);
        $matches = $this->mapper->mapToList($stmt->fetchAll());

        // On regroupe : ['QF' => [...], 'SF' => [...], 'F' => [...]]
        // La vue n'a plus qu'à itérer par round dans le bon ordre.
        $byRound = [];
        foreach ($matches as $m) {
            $byRound[$m->getRound()][] = $m;
        }
        return $byRound;
    }

    // ----------------------------------------------------------------
    // Matchs d'un groupe précis (tableau de classement).

    public function findByGroup(int $idGroupe): array
    {
        $pdo  = $this->database->getConnection();
        $stmt = $pdo->prepare(
            $this->baseQuery() . "
            WHERE m.id_groupe = ?
            ORDER BY m.leg, m.id_match
            "
        );
        $stmt->execute([$idGroupe]);

        return $this->mapper->mapToList($stmt->fetchAll());
    }

    // ----------------------------------------------------------------
    // Matchs en litige — utile pour l'admin qui doit trancher.

    public function findDisputed(): array
    {
        $pdo  = $this->database->getConnection();
        $stmt = $pdo->query(
            $this->baseQuery() . "
            WHERE m.statut = 'disputed'
            ORDER BY m.created_at ASC
            "
        );

        return $this->mapper->mapToList($stmt->fetchAll());
    }

    // ----------------------------------------------------------------
    // Cœur du système — appelé quand un joueur soumet son score.
    //
    // Flux complet :
    //   1. Sauvegarder la soumission du joueur (INSERT ou UPDATE)
    //   2. Si un seul joueur a soumis → statut "submitted", on attend
    //   3. Si les deux ont soumis et scores identiques → "confirmed"
    //      + insertion dans resultat + mise à jour du classement
    //   4. Si les deux ont soumis mais scores différents → "disputed"
    //
    // Retourne une chaîne : 'waiting' | 'confirmed' | 'disputed'
    // Le controller l'utilise pour rediriger avec le bon message.

    public function submitScore(int $idMatch, int $idUser, int $homeScore, int $awayScore): string
    {
        $pdo = $this->database->getConnection();

        // Un score soumis est définitif — on bloque toute re-soumission.
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM submission WHERE id_match = ? AND id_user = ?");
        $stmt->execute([$idMatch, $idUser]);
        if ($stmt->fetchColumn() > 0) {
            return 'already_submitted';
        }

        $stmt = $pdo->prepare("
            INSERT INTO submission (home_score, away_score, id_match, id_user)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$homeScore, $awayScore, $idMatch, $idUser]);

        // On vérifie combien de joueurs ont soumis pour ce match.
        // Tant qu'on n'a pas les deux, on attend.
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM submission WHERE id_match = ?");
        $stmt->execute([$idMatch]);

        if ($stmt->fetchColumn() < 2) {
            $pdo->prepare("UPDATE matche SET statut='submitted' WHERE id_match=?")->execute([$idMatch]);
            return 'waiting';
        }

        // Les deux joueurs ont soumis.
        // On récupère la soumission du joueur domicile...
        $stmt = $pdo->prepare("
            SELECT s.home_score, s.away_score, m.home_user_id, m.away_user_id, m.round, m.id_groupe, m.id_cup
            FROM   submission s
            JOIN   matche     m ON m.id_match = s.id_match
            WHERE  s.id_match = ? AND s.id_user = m.home_user_id
        ");
        $stmt->execute([$idMatch]);
        $home = $stmt->fetch();

        // ...et celle du joueur extérieur.
        $stmt = $pdo->prepare("
            SELECT s.home_score, s.away_score
            FROM   submission s
            JOIN   matche     m ON m.id_match = s.id_match
            WHERE  s.id_match = ? AND s.id_user = m.away_user_id
        ");
        $stmt->execute([$idMatch]);
        $away = $stmt->fetch();

        // Si les deux scores concordent → confirmation automatique,
        // pas besoin d'un admin pour valider.
        if ($home['home_score'] === $away['home_score'] && $home['away_score'] === $away['away_score']) {

            // On enregistre le résultat officiel (confirmed_by NULL = auto).
            $pdo->prepare("
                INSERT INTO resultat (home_score, away_score, id_match, confirmed_by)
                VALUES (?, ?, ?, NULL)
            ")->execute([$home['home_score'], $home['away_score'], $idMatch]);

            $pdo->prepare("UPDATE matche SET statut='confirmed' WHERE id_match=?")->execute([$idMatch]);

            // Si c'est un match de groupe, on met à jour le classement
            // immédiatement — pas besoin d'attendre une action manuelle.
            if ($home['round'] === 'group' && $home['id_groupe'] !== null) {
                $this->updateStandings(
                    $pdo,
                    (int) $home['home_user_id'],
                    (int) $home['away_user_id'],
                    (int) $home['home_score'],
                    (int) $home['away_score'],
                    (int) $home['id_cup'],
                    (int) $home['id_groupe']
                );
            }

            return 'confirmed';
        }

        // Les scores ne concordent pas → litige.
        // Un admin devra trancher via le panel C++/Qt.
        $pdo->prepare("UPDATE matche SET statut='disputed' WHERE id_match=?")->execute([$idMatch]);

        return 'disputed';
    }

    // ----------------------------------------------------------------
    // Mise à jour du classement après un match de groupe confirmé.
    // On utilise un seul UPDATE paramétré qu'on exécute deux fois :
    // une fois pour le domicile, une fois pour l'extérieur.
    // Les ? sont : wins, draws, losses, goals_for, goals_against, points
  
    private function updateStandings(
        \PDO $pdo,
        int  $homeUserId,
        int  $awayUserId,
        int  $homeScore,
        int  $awayScore,
        int  $idCup,
        int  $idGroupe
    ): void {
        $sql = "
            UPDATE est_classe_dans
            SET played        = played + 1,
                wins          = wins   + ?,
                draws         = draws  + ?,
                losses        = losses + ?,
                goals_for     = goals_for     + ?,
                goals_against = goals_against + ?,
                points        = points + ?
            WHERE id_user = ? AND id_cup = ? AND phase = 'group'
        ";
        $stmt = $pdo->prepare($sql);

        if ($homeScore > $awayScore) {
            $stmt->execute([1, 0, 0, $homeScore, $awayScore, 3, $homeUserId, $idCup]);
            $stmt->execute([0, 0, 1, $awayScore, $homeScore, 0, $awayUserId, $idCup]);
        } elseif ($awayScore > $homeScore) {
            $stmt->execute([0, 0, 1, $homeScore, $awayScore, 0, $homeUserId, $idCup]);
            $stmt->execute([1, 0, 0, $awayScore, $homeScore, 3, $awayUserId, $idCup]);
        } else {
            $stmt->execute([0, 1, 0, $homeScore, $awayScore, 1, $homeUserId, $idCup]);
            $stmt->execute([0, 1, 0, $awayScore, $homeScore, 1, $awayUserId, $idCup]);
        }
    }
}
