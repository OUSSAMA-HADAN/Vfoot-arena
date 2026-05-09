package vfoot.presenter;

import vfoot.model.Qualifier;
import vfoot.repository.CupRepository;
import vfoot.repository.KnockoutRepository;
import vfoot.view.IKnockoutView;

import java.util.*;

public class KnockoutPresenter {

    private final IKnockoutView      view;
    private final KnockoutRepository knockoutRepo;
    private final CupRepository      cupRepo;

    public KnockoutPresenter(IKnockoutView view, KnockoutRepository knockoutRepo, CupRepository cupRepo) {
        this.view         = view;
        this.knockoutRepo = knockoutRepo;
        this.cupRepo      = cupRepo;
    }

    /**
     * Génère le bracket éliminatoire pour une cup.
     * Vérifie que tous les matchs de groupe sont confirmés avant de continuer.
     */
    public void generateKnockout(int cupId) {
        try {
            if (knockoutRepo.knockoutAlreadyGenerated(cupId)) {
                view.showError("Un bracket éliminatoire existe déjà pour cette cup.");
                return;
            }

            if (!knockoutRepo.allGroupMatchesConfirmed(cupId)) {
                view.showError("Tous les matchs de groupe doivent être confirmés avant de générer le bracket.");
                return;
            }

            int qualCount = knockoutRepo.getQualificationCount(cupId);
            Map<String, List<Qualifier>> byGroup = knockoutRepo.getStandingsByGroup(cupId);

            if (byGroup.isEmpty()) {
                view.showError("Aucun groupe trouvé pour cette cup.");
                return;
            }

            view.showQualifiers(byGroup, qualCount);

            // Construire la liste des qualifiés : interleave groups (A1, B1, A2, B2…)
            // pour que les groupes différents s'affrontent au premier tour
            List<Qualifier> qualifiers = interleaveQualifiers(byGroup, qualCount);

            if (qualifiers.size() < 2) {
                view.showError("Pas assez de qualifiés pour générer un bracket (minimum 2).");
                return;
            }

            List<Integer> qualifierIds = qualifiers.stream()
                    .map(Qualifier::getUserId)
                    .toList();

            knockoutRepo.generateBracket(cupId, qualifierIds);
            cupRepo.updateStatut(cupId, "started");

            // Compte les matchs créés (leg1 + leg2 ou single)
            int matchCount = qualifiers.size() / 2;
            String round = qualifiers.size() >= 16 ? "R16"
                         : qualifiers.size() >= 8  ? "QF"
                         : qualifiers.size() >= 4  ? "SF" : "F";

            view.showBracketGenerated(round, matchCount);

        } catch (Exception e) {
            view.showError(e.getMessage());
        }
    }

    /**
     * Calcule les vainqueurs du round actif et crée les matchs du round suivant.
     * Si le round est la finale, clôture la cup.
     */
    public void advanceRound(int cupId) {
        try {
            String currentRound = knockoutRepo.getCurrentKnockoutRound(cupId);

            if (currentRound == null) {
                view.showError("Aucune phase éliminatoire active pour cette cup. Générez d'abord le bracket.");
                return;
            }

            if (!knockoutRepo.allRoundMatchesConfirmed(cupId, currentRound)) {
                view.showError("Tous les matchs du round " + currentRound + " doivent être confirmés avant d'avancer.");
                return;
            }

            List<Integer> winnerIds = knockoutRepo.computeWinners(cupId, currentRound);

            if (winnerIds.isEmpty()) {
                view.showError("Impossible de calculer les vainqueurs. Vérifiez les résultats.");
                return;
            }

            List<String> winnerNames = getWinnerNames(winnerIds, cupId);
            view.showWinners(currentRound, winnerNames);

            // Finale terminée → clôturer la cup
            if (currentRound.equals("F")) {
                cupRepo.updateStatut(cupId, "finished");
                view.showCupFinished(winnerNames.isEmpty() ? "Inconnu" : winnerNames.get(0));
                return;
            }

            String newRound = knockoutRepo.advanceToNextRound(cupId, currentRound, winnerIds);
            view.showRoundAdvanced(newRound);

        } catch (Exception e) {
            view.showError(e.getMessage());
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Interleave les qualifiés de chaque groupe :
     * [A1, B1, C1, A2, B2, C2, ...] pour éviter que deux joueurs du même groupe
     * se retrouvent au premier tour.
     */
    private List<Qualifier> interleaveQualifiers(Map<String, List<Qualifier>> byGroup, int qualCount) {
        List<Qualifier> result = new ArrayList<>();
        for (int rank = 0; rank < qualCount; rank++) {
            for (List<Qualifier> groupPlayers : byGroup.values()) {
                if (rank < groupPlayers.size()) {
                    result.add(groupPlayers.get(rank));
                }
            }
        }
        return result;
    }

    /** Récupère les usernames des vainqueurs depuis la DB. */
    private List<String> getWinnerNames(List<Integer> winnerIds, int cupId) {
        try {
            Map<String, List<Qualifier>> byGroup = knockoutRepo.getStandingsByGroup(cupId);
            Map<Integer, String> nameMap = new HashMap<>();
            byGroup.values().forEach(list -> list.forEach(q -> nameMap.put(q.getUserId(), q.getUsername())));

            List<String> names = new ArrayList<>();
            for (int id : winnerIds) {
                names.add(nameMap.getOrDefault(id, "Joueur #" + id));
            }
            return names;
        } catch (Exception e) {
            return winnerIds.stream().map(id -> "Joueur #" + id).toList();
        }
    }
}
