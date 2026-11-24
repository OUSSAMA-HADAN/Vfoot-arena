package vfoot.view;

import vfoot.model.Cup;
import vfoot.model.Match;
import vfoot.model.Standing;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.time.format.DateTimeParseException;
import java.util.*;

public class ConsoleView {

    private final Scanner scanner;

    public ConsoleView(Scanner scanner) {
        this.scanner = scanner;
    }

    // ── Auth ─────────────────────────────────────────────────────
    public String askEmail() {
        System.out.print("Email : ");
        return scanner.nextLine().trim();
    }

    public String askPassword() {
        System.out.print("Mot de passe : ");
        return scanner.nextLine();
    }

    // ── Menu ─────────────────────────────────────────────────────
    public void showMenu(String username) {
        System.out.println("\n╔══════════════════════════════════╗");
        System.out.println("║       VFoot — Organisateur       ║");
        System.out.println("╠══════════════════════════════════╣");
        System.out.printf ("║  Connecté : %-20s║%n", username);
        System.out.println("╠══════════════════════════════════╣");
        System.out.println("║  1. Mes cups                     ║");
        System.out.println("║  2. Créer une cup                ║");
        System.out.println("║  3. Lancer phase éliminatoire    ║");
        System.out.println("║  4. Gérer les litiges            ║");
        System.out.println("║  5. Quitter                      ║");
        System.out.println("╚══════════════════════════════════╝");
    }

    // ── Cups ─────────────────────────────────────────────────────
    public void showCups(List<Cup> cups) {
        if (cups.isEmpty()) {
            showMessage("Vous n'avez aucune cup.");
            return;
        }
        System.out.println("\n── Vos cups ──────────────────────");
        cups.forEach(c -> System.out.println("  " + c));
        System.out.println("──────────────────────────────────");
        //Java appelle automatiquement : c.toString()
        //equivalant a System.out.println(c.toString());
    }

    // ── Classement ───────────────────────────────────────────────
    public void showStandings(List<Standing> standings) {
        if (standings.isEmpty()) { showMessage("Aucun classement disponible."); return; }

        String currentGroupe = null;
        System.out.println();
        for (Standing s : standings) {
            if (!s.getGroupeNom().equals(currentGroupe)) {
                currentGroupe = s.getGroupeNom();
                System.out.printf("%n  ── Groupe %s ─────────────────────────────────────%n", currentGroupe);
                System.out.printf("  %-15s %3s %3s %3s %3s %5s %3s%n",
                        "Joueur", "J", "G", "N", "P", "Buts", "Pts");
            }
            System.out.printf("  %-15s %3d %3d %3d %3d %2d:%-2d %3d%n",
                    s.getUsername(), s.getPlayed(), s.getWins(),
                    s.getDraws(), s.getLosses(),
                    s.getGoalsFor(), s.getGoalsAgainst(), s.getPoints());
        }
        System.out.println();
    }

    // ── Litiges ──────────────────────────────────────────────────
    public void showDisputedMatches(List<Match> matches) {
        if (matches.isEmpty()) { showMessage("Aucun litige pour cette cup."); return; }
        System.out.println("\n── Matchs en litige ──────────────");
        for (Match m : matches) {
            System.out.printf("  [%d] %s vs %s (%s)%n",
                    m.getIdMatch(), m.getHomeName(), m.getAwayName(), m.getRound());
            System.out.printf("       %s a soumis : %d - %d%n",
                    m.getHomeName(), m.getHomeSubHome(), m.getHomeSubAway());
            System.out.printf("       %s a soumis : %d - %d%n",
                    m.getAwayName(), m.getAwaySubHome(), m.getAwaySubAway());
        }
        System.out.println("──────────────────────────────────");
    }

    // ── Saisie cup ───────────────────────────────────────────────
    public String askString(String prompt) {
        while (true) {
            System.out.print(prompt);
            String val = scanner.nextLine().trim();
            if (!val.isEmpty()) return val;
            showError("Champ obligatoire.");
        }
    }

    public String askVisibilite() {
        while (true) {
            System.out.print("  Visibilite (1=public / 2=prive) : ");
            String v = scanner.nextLine().trim();
            if (v.equals("1")) return "public";
            if (v.equals("2")) return "private";
            showError("Entrez 1 ou 2.");
        }
    }

    public int askIntInRange(String prompt, int min, int max) {
        while (true) {
            System.out.print(prompt);
            try {
                int v = Integer.parseInt(scanner.nextLine().trim());
                if (v >= min && v <= max) return v;
                showError("Valeur entre " + min + " et " + max + " attendue.");
            } catch (NumberFormatException e) {
                showError("Entier attendu.");
            }
        }
    }

    // ── Utilitaires ───────────────────────────────────────────────
    public LocalDateTime askDeadline() {
        DateTimeFormatter fmt = DateTimeFormatter.ofPattern("dd/MM/yyyy");
        while (true) {
            System.out.print("  Deadline (JJ/MM/AAAA) : ");
            String input = scanner.nextLine().trim();
            try {
                return LocalDate.parse(input, fmt).atTime(23, 59);
            } catch (DateTimeParseException e) {
                showError("Format invalide — exemple : 25/12/2025");
            }
        }
    }

    public int askInt(String prompt) {
        while (true) {
            System.out.print(prompt);
            try { return Integer.parseInt(scanner.nextLine().trim()); }
            catch (NumberFormatException e) { showError("Entier attendu."); }
        }
    }

    public void showMessage(String msg) {
        System.out.println("  ✔ " + msg);
    }

    public void showError(String msg) {
        System.out.println("  ✖ " + msg);
    }
}
