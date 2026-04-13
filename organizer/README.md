# VFoot Arena — Organizer (Java MVP)

Application console Java pour les **organisateurs** de la plateforme VFoot Arena.  
Elle se connecte à la même base de données MySQL que le côté PHP (player) et permet de gérer les cups, surveiller les matchs et résoudre les litiges de scores.

---

## Qu'est-ce que le pattern MVP ?

MVP = **Model — View — Presenter**  
C'est une façon d'organiser le code pour que chaque partie ait un rôle précis et ne fasse qu'une seule chose.

```
┌─────────────┐     appelle      ┌───────────────┐     lit/écrit    ┌─────────────┐
│    View     │ ◄──────────────► │   Presenter   │ ◄──────────────► │    Model    │
│  (affiche)  │                  │  (logique)    │                  │  (données)  │
└─────────────┘                  └───────────────┘                  └─────────────┘
```

| Couche | Rôle | Exemple dans le projet |
|--------|------|------------------------|
| **Model** | Représente les données (un objet Cup, un objet Match…) | `Cup.java`, `Match.java` |
| **Repository** | Parle à la base de données (requêtes SQL) | `CupRepository.java` |
| **Presenter** | Contient la logique métier — reçoit une action, appelle le Repository, dit à la View quoi afficher | `CupPresenter.java` |
| **View** | Affiche le résultat à l'écran — ne décide de rien | `ConsoleCupView.java` |

> **Règle d'or** : le Presenter ne sait pas que la View est une console. Il appelle uniquement une interface (`ICupView`). Si demain on passe à JavaFX ou une interface web, seule la View change — le Presenter reste intact.

---

## Structure des fichiers

```
organizer/
├── pom.xml                                         ← configuration Maven (dépendances, build)
├── README.md
└── src/
    └── main/
        ├── resources/
        │   └── config.properties                   ← identifiants de connexion à la DB
        └── java/vfoot/
            │
            ├── Main.java                           ← point d'entrée, menu console
            │
            ├── config/
            │   └── Database.java                   ← connexion JDBC unique (singleton)
            │
            ├── model/                              ← objets de données (POJOs)
            │   ├── Cup.java
            │   ├── Match.java
            │   └── User.java
            │
            ├── repository/                         ← requêtes SQL via JDBC
            │   ├── CupRepository.java
            │   └── MatchRepository.java
            │
            ├── view/                               ← interfaces (contrats d'affichage)
            │   ├── IDashboardView.java
            │   ├── ICupView.java
            │   ├── IMatchView.java
            │   └── console/                        ← implémentations console
            │       ├── ConsoleDashboardView.java
            │       ├── ConsoleCupView.java
            │       └── ConsoleMatchView.java
            │
            └── presenter/                          ← logique métier
                ├── DashboardPresenter.java
                ├── CupPresenter.java
                └── MatchPresenter.java
```

---

## Flux d'exécution — exemple concret

L'utilisateur choisit **"6 — Matchs en litige"** dans le menu.

```
Main.java
  └── matchPresenter.listDisputed()
        └── repo.findDisputed()              ← requête SQL SELECT ... WHERE statut='disputed'
              └── retourne List<Match>
        └── view.showDisputedMatches(list)   ← ConsoleMatchView affiche dans le terminal
```

Le Presenter ne touche jamais `System.out`.  
La View ne touche jamais la base de données.  
Chaque classe fait une seule chose.

---

## Configuration de la base de données

Ouvrir `src/main/resources/config.properties` :

```properties
db.url=jdbc:mysql://localhost:3306/efootball_tournament?useSSL=false&serverTimezone=UTC&characterEncoding=UTF-8
db.user=root
db.password=
```

Adapter `db.user` et `db.password` selon votre installation MySQL/XAMPP.  
Le nom de la base (`efootball_tournament`) doit correspondre à celui créé via le script SQL dans `/database`.

---

## Lancer l'application

### Avec IntelliJ IDEA (recommandé)

1. Télécharger **IntelliJ IDEA Community** (gratuit) sur jetbrains.com/idea
2. **File → Open** → sélectionner le dossier `organizer/`
3. IntelliJ détecte `pom.xml` et télécharge automatiquement Maven et le driver MySQL
4. Cliquer sur le bouton **🔄 Load Maven Changes** si demandé
5. Ouvrir `Main.java` → cliquer sur le triangle vert ▶ à côté de `main()`

### Avec Maven en ligne de commande

```bash
cd organizer/

# Compiler + créer le jar exécutable
mvn package

# Lancer
java -jar target/organizer-1.0.0.jar
```

> Si Maven n'est pas installé : télécharger `apache-maven-3.9.x-bin.zip` sur maven.apache.org,
> extraire dans `C:\maven`, ajouter `C:\maven\bin` à la variable d'environnement `PATH`.

---

## Fonctionnalités disponibles

```
┌─────────────────────────────────┐
│  1. Dashboard                   │  ← stats globales (cups en cours, matchs confirmés...)
│  2. Lister les cups             │
│  3. Créer une cup               │
│  4. Changer statut d'une cup    │  ← pending / started / finished
│  5. Lister tous les matchs      │
│  6. Matchs en litige            │  ← affiche les scores contradictoires des deux joueurs
│  7. Résoudre un litige          │  ← l'organisateur impose le score final
│  0. Quitter                     │
└─────────────────────────────────┘
```

---

## Dépendances

| Dépendance | Version | Rôle |
|------------|---------|------|
| `com.mysql:mysql-connector-j` | 8.3.0 | Driver JDBC pour se connecter à MySQL |
| Java | 21+ | Langage |
| Maven | 3.9+ | Build, gestion des dépendances |

Toutes les dépendances sont déclarées dans `pom.xml`. Maven les télécharge automatiquement depuis Maven Central au premier `mvn package`.

---

## Ce que ce projet n'est pas

- Ce n'est **pas** une application web — il n'y a pas de serveur HTTP
- Ce n'est **pas** Spring ou Hibernate — aucun framework, JDBC pur
- Ce n'est **pas** une version finale — c'est l'architecture de base que l'équipe Java va faire évoluer

---

## Équipe

| Branche | Équipe | Technologie |
|---------|--------|-------------|
| `player` | Côté joueur | PHP (MVC maison) |
| `organizer` | Côté organisateur | Java (MVP) |
| `admin` | Côté administration | C++ (MVVM + Qt) |

Base de données partagée : `efootball_tournament` (MySQL)  
Documentation du schéma : `/database/`
