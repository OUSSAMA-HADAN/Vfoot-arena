package vfoot.presenter;

import vfoot.repository.CupRepository;
import vfoot.repository.GroupRepository;
import vfoot.view.IGroupView;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

public class GroupPresenter {

    private final IGroupView    view;
    private final GroupRepository groupRepo;
    private final CupRepository   cupRepo;

    public GroupPresenter(IGroupView view, GroupRepository groupRepo, CupRepository cupRepo) {
        this.view      = view;
        this.groupRepo = groupRepo;
        this.cupRepo   = cupRepo;
    }

    /**
     * Affiche les participants d'une cup avant de générer les groupes.
     */
    public void showParticipants(int cupId) {
        try {
            List<String[]> participants = groupRepo.getParticipantsWithNames(cupId);
            if (participants.isEmpty()) {
                view.showError("Aucun joueur inscrit dans cette cup.");
                return;
            }
            view.showParticipants(participants);
        } catch (Exception e) {
            view.showError(e.getMessage());
        }
    }

    /**
     * Génère les groupes aléatoirement et crée tous les matchs aller-retour.
     * @param numGroups nombre de groupes voulus (ex: 2, 4, 8)
     */
    public void generateGroups(int cupId, int numGroups) {
        try {
            if (groupRepo.groupsAlreadyExist(cupId)) {
                view.showError("Des groupes existent déjà pour cette cup.");
                return;
            }

            List<String[]> participants = groupRepo.getParticipantsWithNames(cupId);

            if (participants.isEmpty()) {
                view.showError("Aucun joueur inscrit dans cette cup.");
                return;
            }

            if (participants.size() < numGroups * 2) {
                view.showError("Pas assez de joueurs — il faut au moins " + (numGroups * 2) + " joueurs pour " + numGroups + " groupes.");
                return;
            }

            // Mélange aléatoire pour un tirage au sort équitable
            List<Integer> playerIds = new ArrayList<>();
            for (String[] p : participants) playerIds.add(Integer.parseInt(p[0]));
            Collections.shuffle(playerIds);

            view.showParticipants(participants);
            view.showInfo("Tirage au sort en cours...");

            groupRepo.generate(cupId, playerIds, numGroups);
            cupRepo.updateStatut(cupId, "started");

            // Calcul du nombre de matchs : chaque groupe fait n*(n-1) matchs
            int playersPerGroup = participants.size() / numGroups;
            int matchesPerGroup = playersPerGroup * (playersPerGroup - 1);
            int totalMatches    = matchesPerGroup * numGroups;

            view.showGroupsGenerated(numGroups, totalMatches);

        } catch (Exception e) {
            view.showError(e.getMessage());
        }
    }
}
