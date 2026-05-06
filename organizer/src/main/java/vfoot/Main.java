package vfoot;

import vfoot.presenter.CupPresenter;
import vfoot.presenter.DashboardPresenter;
import vfoot.presenter.MatchPresenter;
import vfoot.repository.CupRepository;
import vfoot.repository.MatchRepository;
import vfoot.view.console.ConsoleCupView;
import vfoot.view.console.ConsoleDashboardView;
import vfoot.view.console.ConsoleMatchView;

import java.util.Scanner;

/**
 * Point d'entrée — câble les Presenters avec leurs Views et Repositories,
 * puis lance la boucle de menu console.
 *
 * Flux MVP :
 *   Main  →  Presenter  →  Repository  (lit/écrit DB)
 *                     ↓
 *                   View   (affiche le résultat)
 */
public class Main {

    public static void main(String[] args) {
        // ── Repositories (accès données) ───────────────────────
        CupRepository   cupRepo   = new CupRepository();
        MatchRepository matchRepo = new MatchRepository();

        // ── Views (implémentations console) ───────────────────
        ConsoleDashboardView dashView  = new ConsoleDashboardView();
        ConsoleCupView       cupView   = new ConsoleCupView();
        ConsoleMatchView     matchView = new ConsoleMatchView();

        // ── Presenters (logique métier) ────────────────────────
        DashboardPresenter dashPresenter  = new DashboardPresenter(dashView, cupRepo, matchRepo);
        CupPresenter       cupPresenter   = new CupPresenter(cupView, cupRepo);
        MatchPresenter     matchPresenter = new MatchPresenter(matchView, matchRepo);

        // ── Boucle de menu ────────────────────────────────────
        Scanner sc = new Scanner(System.in);

        // Afficher le dashboard au démarrage
        dashPresenter.loadStats();

        while (true) {
            printMenu();
            System.out.print("Choix > ");
            String input = sc.nextLine().trim();

            switch (input) {
                // ── Dashboard ─────────────────────────────────
                case "1" -> dashPresenter.loadStats();

                // ── Cups ──────────────────────────────────────
                case "2" -> cupPresenter.listCups();

                case "3" -> {
                    System.out.print("  Titre       : "); String titre = sc.nextLine().trim();
                    System.out.print("  Min joueurs : "); int min = parseInt(sc.nextLine());
                    System.out.print("  Max joueurs : "); int max = parseInt(sc.nextLine());
                    // id organizer fixé à 1 pour la démo — à remplacer par login réel
                    cupPresenter.createCup(titre, min, max, 1);
                }

                case "4" -> {
                    System.out.print("  ID cup    : "); int id = parseInt(sc.nextLine());
                    System.out.print("  Statut    (pending/started/finished) : ");
                    String statut = sc.nextLine().trim();
                    cupPresenter.changeStatut(id, statut);
                }

                // ── Matchs ────────────────────────────────────
                case "5" -> matchPresenter.listAll();

                case "6" -> matchPresenter.listDisputed();

                case "7" -> {
                    System.out.print("  ID match  : "); int id    = parseInt(sc.nextLine());
                    System.out.print("  Score domicile : "); int home = parseInt(sc.nextLine());
                    System.out.print("  Score extérieur: "); int away = parseInt(sc.nextLine());
                    matchPresenter.resolveDispute(id, home, away);
                }

                // ── Quitter ───────────────────────────────────
                case "0" -> {
                    System.out.println("Au revoir.");
                    sc.close();
                    return;
                }

                default -> System.out.println("Option invalide.");
            }
        }
    }

    private static void printMenu() {
        System.out.println("┌─────────────────────────────────┐");
        System.out.println("│  1. Dashboard                   │");
        System.out.println("│  2. Lister les cups             │");
        System.out.println("│  3. Créer une cup               │");
        System.out.println("│  4. Changer statut d'une cup    │");
        System.out.println("│  5. Lister tous les matchs      │");
        System.out.println("│  6. Matchs en litige            │");
        System.out.println("│  7. Résoudre un litige          │");
        System.out.println("│  0. Quitter                     │");
        System.out.println("└─────────────────────────────────┘");
    }

    private static int parseInt(String s) {
        try { return Integer.parseInt(s.trim()); }
        catch (NumberFormatException e) { return 0; }
    }
}
