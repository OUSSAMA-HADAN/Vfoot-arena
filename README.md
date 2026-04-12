# VFoot Arena — Documentation complète

Plateforme web de gestion de tournois eFootball (football virtuel).  
Projet académique de synthèse — 3ème année Génie Informatique, EHEI Oujda.  
Développé en PHP pur, sans framework externe, architecture MVC from scratch.

---

## Table des matières

1. [C'est quoi ce projet ?](#1-cest-quoi-ce-projet-)
2. [Comment fonctionne un site web ? (les bases)](#2-comment-fonctionne-un-site-web--les-bases)
3. [C'est quoi le MVC ?](#3-cest-quoi-le-mvc-)
4. [Le flux complet de VFoot Arena](#4-le-flux-complet-de-vfoot-arena)
5. [Structure des dossiers — chaque fichier expliqué](#5-structure-des-dossiers--chaque-fichier-expliqué)
6. [La base de données — chaque table expliquée](#6-la-base-de-données--chaque-table-expliquée)
7. [Le système de routes](#7-le-système-de-routes)
8. [Le système de middleware](#8-le-système-de-middleware)
9. [Gestion des variables d'environnement (.env)](#9-gestion-des-variables-denvironnement-env)
10. [Fonctionnalités du projet](#10-fonctionnalités-du-projet)
11. [Équipe et répartition](#11-équipe-et-répartition)
12. [Lancer le projet en local](#12-lancer-le-projet-en-local)

---

## 1. C'est quoi ce projet ?

VFoot Arena est une plateforme web qui permet à des joueurs de participer à des tournois de football virtuel (eFootball).

Un tournoi (appelé **cup**) fonctionne en deux phases :
- **Phase de groupes** : les joueurs sont répartis en groupes de 4 à 5. Chacun joue contre tous les autres. 3 points pour une victoire, 1 pour un nul, 0 pour une défaite. Les 2 meilleurs de chaque groupe passent à la phase suivante.
- **Phase knockout** : élimination directe. Les matchs sont générés automatiquement (huitièmes, quarts, demi-finales, finale).

Chaque joueur soumet son score après un match. Si les deux joueurs soumettent le même score → confirmé automatiquement. Si les scores ne concordent pas → litige → l'organisateur tranche.

---

## 2. Comment fonctionne un site web ? (les bases)

Avant de comprendre le code, il faut comprendre ce qui se passe quand tu tapes une URL dans un navigateur.

```
Tu tapes : http://localhost/vfoot/login

1. Le navigateur envoie une REQUÊTE au serveur (Apache)
2. Le serveur cherche le fichier correspondant
3. PHP génère une RÉPONSE (du HTML)
4. Le navigateur affiche le HTML reçu
```

### Le problème sans routeur

Sans système de routage, chaque page = un fichier PHP séparé :
```
/login.php
/register.php
/cups.php
/profile.php
```
C'est incontrôlable dès que le projet grandit. Pas de logique centralisée, pas de sécurité cohérente.

### La solution : un point d'entrée unique

VFoot Arena utilise **un seul fichier d'entrée** : `public/index.php`.  
Toutes les URLs passent par lui, et c'est le Router qui décide quoi faire.

```
http://localhost/vfoot/login   → index.php → Router → AuthController
http://localhost/vfoot/cups    → index.php → Router → CupController
http://localhost/vfoot/profile → index.php → Router → ProfileController
```

C'est ça le principe **MVC avec routeur**.

---

## 3. C'est quoi le MVC ?

MVC = **M**odel — **V**iew — **C**ontroller

C'est une façon d'organiser le code en séparant les responsabilités :

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   MODEL     │     │   VIEW      │     │ CONTROLLER  │
│             │     │             │     │             │
│ Parle à la  │     │ Affiche le  │     │ Fait le lien│
│ base de     │◄────│ HTML à      │◄────│ entre les   │
│ données     │     │ l'utilisateur│    │ deux        │
└─────────────┘     └─────────────┘     └─────────────┘
```

**Analogie restaurant :**
- **Controller** = le serveur (reçoit la commande, coordonne)
- **Model** = la cuisine (prépare les données depuis la base)
- **View** = l'assiette (présente le résultat à l'utilisateur)

**Exemple concret — afficher la liste des cups :**

```
1. L'utilisateur va sur /cups
2. Router → CupController::index()
3. CupController demande à CupModel : "donne-moi toutes les cups ouvertes"
4. CupModel fait une requête SQL sur la table `cup`
5. CupController reçoit les données et les passe à la View
6. La View affiche le HTML avec la liste des cups
```

---

## 4. Le flux complet de VFoot Arena

```
NAVIGATEUR
    │
    │  GET /cups
    ▼
┌─────────────────────────────────────────────────────┐
│  .htaccess (public/)                                │
│  Redirige TOUT vers index.php                       │
└─────────────────────────┬───────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────┐
│  public/index.php                                   │
│  - Env::load('.env')      ← charge les credentials  │
│  - charge autoload.php    ← charge les classes      │
│  - crée le Router                                   │
│  - enregistre toutes les routes                     │
│  - appelle Router::dispatch()                       │
└─────────────────────────┬───────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────┐
│  app/Core/Router.php — dispatch()                   │
│  - lit la méthode HTTP (GET) et l'URI (/cups)       │
│  - cherche la route correspondante                  │
│  - trouve : GET /cups → middleware:['auth']         │
│             → CupController@index                   │
└─────────────────────────┬───────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────┐
│  app/Middleware/AuthMiddleware.php                  │
│  - Session::get('user_id') → connecté ?             │
│  - NON → Response::redirect('/login') + exit        │
│  - OUI → on continue ✓                              │
└─────────────────────────┬───────────────────────────┘
                          │  (utilisateur connecté)
                          ▼
┌─────────────────────────────────────────────────────┐
│  app/Controllers/CupController.php                  │
│  - reçoit la requête                                │
│  - appelle CupModel pour récupérer les données      │
└──────────┬──────────────────────────────────────────┘
           │
           ▼
┌─────────────────────────────────────────────────────┐
│  app/Models/CupModel.php                            │
│  - fait une requête SQL via PDO                     │
│  - retourne les données au Controller               │
└──────────┬──────────────────────────────────────────┘
           │
           ▼
┌─────────────────────────────────────────────────────┐
│  app/Core/Database.php (PDO singleton)              │
│  - connexion MySQL (une seule instance)             │
│  - exécute la requête sur efootball_tournament      │
└──────────┬──────────────────────────────────────────┘
           │
           ▼
┌─────────────────────────────────────────────────────┐
│  MySQL — table `cup`                                │
│  retourne les lignes de la base                     │
└─────────────────────────────────────────────────────┘
           │
           │ (données remontent vers le Controller)
           ▼
┌─────────────────────────────────────────────────────┐
│  app/Controllers/CupController.php                  │
│  - passe les données à la View                      │
└──────────┬──────────────────────────────────────────┘
           │
           ▼
┌─────────────────────────────────────────────────────┐
│  app/Views/cups/index.php                           │
│  - génère le HTML avec les données reçues           │
└──────────┬──────────────────────────────────────────┘
           │
           ▼
        NAVIGATEUR
        affiche la page HTML
```

---

## 5. Structure des dossiers — chaque fichier expliqué

```
vfoot/
├── autoload.php                  ← charge automatiquement toutes les classes
├── efootball_tournament_v2.sql
├── .env                          ← credentials locaux (jamais commit)
├── .env.example                  ← modèle partagé (commit)
├── .gitignore
├── config/
│   └── database.php              ← lit depuis .env via Env::get()
├── public/
│   ├── .htaccess                 ← redirige tout vers index.php
│   ├── index.php                 ← point d'entrée unique
│   └── assets/
└── app/
    ├── Core/                     ← moteur MVC
    │   ├── Env.php               ← lecteur .env fait maison
    │   ├── Database.php          ← singleton PDO
    │   ├── Router.php            ← routes + dispatch + middleware
    │   ├── Request.php           ← wrapper $_GET, $_POST, $_SERVER
    │   ├── Response.php          ← redirect, json, abort
    │   ├── Session.php           ← wrapper $_SESSION + flash messages
    │   ├── Controller.php        ← classe parente des controllers
    │   ├── Model.php             ← classe parente des models
    │   └── View.php              ← chargement des templates
    ├── Middleware/               ← filtres exécutés avant les controllers
    │   ├── AuthMiddleware.php    ← bloque si non connecté
    │   └── GuestMiddleware.php   ← bloque si déjà connecté
    ├── Controllers/
    │   ├── AuthController.php
    │   ├── CupController.php
    │   ├── MatchController.php
    │   └── ProfileController.php
    ├── Models/
    │   ├── UserModel.php
    │   ├── CupModel.php
    │   ├── MatchModel.php
    │   ├── GroupModel.php
    │   ├── StatsModel.php
    │   └── TropheeModel.php
    └── Views/
        ├── layouts/
        │   └── main.php
        ├── auth/
        ├── cups/
        └── profile/
```

---

### `autoload.php`
Quand PHP rencontre une classe (ex: `new CupController()`), il a besoin de savoir où elle est définie.  
Sans autoload, il faudrait écrire `require 'app/Controllers/CupController.php'` partout.  
`autoload.php` enregistre une fonction qui cherche automatiquement le bon fichier selon le nom de la classe.

---

### `config/database.php`
Contient uniquement les identifiants de connexion MySQL :
- hôte, nom de la base, utilisateur, mot de passe
Séparé du reste pour ne jamais mettre ces infos dans le code métier.

---

### `public/.htaccess`
Fichier de configuration Apache. Il dit :
> "Si le fichier ou dossier demandé n'existe pas, redirige vers index.php"

C'est ce qui permet d'avoir des URLs propres comme `/login` au lieu de `/index.php?page=login`.

---

### `public/index.php`
Le **seul fichier** accessible directement depuis le navigateur.  
Son rôle :
1. Charger `autoload.php`
2. Créer le Router
3. Enregistrer toutes les routes (`/login`, `/cups`, etc.)
4. Appeler `dispatch()` pour traiter la requête actuelle

---

### `app/Core/Database.php`
Gère la connexion à MySQL via **PDO** (PHP Data Objects).  
C'est un **Singleton** — une seule instance de connexion pour toute l'application.

> **Pourquoi PDO ?** PDO protège contre les injections SQL grâce aux requêtes préparées. C'est la méthode sécurisée standard en PHP.

---

### `app/Core/Router.php`
Maintient une liste de routes : `méthode HTTP + URL → [Controller, méthode]`  
Sa méthode `dispatch()` lit l'URL courante et appelle le bon controller.

---

### `app/Core/Request.php`
Wrapper propre autour des variables globales PHP :
- `$_GET` → paramètres dans l'URL (`?id=5`)
- `$_POST` → données d'un formulaire
- `$_SERVER` → infos du serveur (méthode HTTP, URL...)

Au lieu d'accéder directement à `$_POST['email']` partout, on passe par `$request->post('email')`.

---

### `app/Core/Response.php`
Gère les réponses : redirections (`header('Location: ...')`) et réponses JSON pour les éventuelles requêtes AJAX.

---

### `app/Core/Controller.php`
Classe parente de tous les controllers.  
Contient les méthodes communes : charger une vue, rediriger, vérifier si l'utilisateur est connecté...  
Tous les controllers héritent de cette classe avec `extends Controller`.

---

### `app/Core/Model.php`
Classe parente de tous les models.  
Contient les opérations CRUD de base (Create, Read, Update, Delete) via PDO.  
Tous les models héritent de cette classe avec `extends Model`.

---

### `app/Core/View.php`
Charge et affiche les fichiers template PHP depuis `app/Views/`.  
Injecte les données passées par le Controller dans le template.

---

### `app/Core/Session.php`
Wrapper autour de `$_SESSION` PHP.  
Gère : connexion utilisateur, messages flash (erreurs, succès), protection CSRF.

---

### `app/Controllers/`

| Fichier | Responsabilité |
|---|---|
| `AuthController.php` | Afficher login/register, traiter les formulaires, déconnecter |
| `CupController.php` | Lister les cups, afficher le détail, rejoindre avec code |
| `MatchController.php` | Afficher les matchs d'un joueur, soumettre un score |
| `ProfileController.php` | Afficher le profil public, les stats, les trophées |

---

### `app/Models/`

| Fichier | Tables MySQL |
|---|---|
| `UserModel.php` | `users` |
| `CupModel.php` | `cup`, `participe` |
| `MatchModel.php` | `matche`, `submission`, `resultat` |
| `GroupModel.php` | `groupe`, `est_classe_dans` |
| `StatsModel.php` | `stats_joueur` |
| `TropheeModel.php` | `trophee`, `remporte` |

---

### `app/Views/layouts/main.php`
Le squelette HTML commun à toutes les pages : `<html>`, `<head>`, navigation, footer.  
Chaque page individuelle est injectée à l'intérieur de ce layout.

---

## 6. La base de données — chaque table expliquée

Base : `efootball_tournament`

---

### `users` — Les utilisateurs
```
id_user | email | password | username | role | is_banned | created_at
```
Tous les comptes : joueurs et admins. Le `password` est stocké hashé (jamais en clair).

---

### `cup` — Les tournois
```
id_cup | titre | visibilite | join_code | min_players | max_players | statut | qualification | created_at | created_by
```
- `visibilite` : `public` (libre) ou `private` (code requis)
- `statut` : `draft → open → started → finished / cancelled`
- `qualification` : nombre de joueurs par groupe qui passent au knockout (défaut : 2)

---

### `groupe` — Les groupes d'une cup
```
id_groupe | nom | id_cup
```
Ex : "Groupe A", "Groupe B"... Rattachés à une cup.

---

### `matche` — Les matchs
```
id_match | round | statut | deadline | id_cup | id_groupe | next_match_id | home_user_id | away_user_id
```
- `round` : `group`, `R16`, `QF`, `SF`, `F`
- `id_groupe` : rempli uniquement en phase de groupes, NULL en knockout
- `next_match_id` : auto-référence — pointe vers le match suivant dans le bracket knockout
- `statut` : `pending → submitted → confirmed` ou `disputed → confirmed` ou `forfeited`

---

### `submission` — Les scores soumis par les joueurs
```
id_submission | home_score | away_score | date_soumission | id_match | id_user
```
Chaque joueur soumet son score. Un seul par joueur par match (contrainte UNIQUE).

---

### `resultat` — Le score final confirmé
```
id_resultat | home_score | away_score | date_confirmation | id_match | confirmed_by
```
Créé quand les deux soumissions concordent (auto) ou quand l'organisateur tranche (manuel).  
Un seul par match (contrainte UNIQUE).

---

### `participe` — Qui participe à quelle cup
```
id_user | id_cup | role | date_adhesion
```
Table de liaison. `role` : `organizer` ou `player` dans cette cup spécifique.

---

### `est_classe_dans` — Classement en phase de groupes
```
id_user | id_groupe | played | wins | draws | losses | goals_for | goals_against | points
```
Mis à jour après chaque match confirmé. Sert à afficher le tableau de classement d'un groupe.

---

### `stats_joueur` — Statistiques globales du joueur
```
id_stats | id_user | phase | wins | draws | losses | goals_for | goals_against | total_matches
```
- `phase` : `group` ou `knockout`
- Sert au profil public du joueur (stats agrégées sur toutes ses cups)

---

### `trophee` — Les trophées disponibles
```
id_trophee | nom | icone
```
Ex : "Vainqueur de Cup", "Champion du groupe"...

---

### `remporte` — Qui a gagné quoi
```
id_user | id_trophee | date_obtention
```
Table de liaison entre joueurs et trophées.

---

## 7. Le système de routes

Une route = une URL + une méthode HTTP + un controller + une méthode PHP.

```php
// Dans index.php
$router->get('/cups',          [CupController::class,     'index']);
$router->get('/cups/{id}',     [CupController::class,     'show']);
$router->post('/cups/join',    [CupController::class,     'join']);
$router->get('/login',         [AuthController::class,    'showLogin']);
$router->post('/login',        [AuthController::class,    'login']);
$router->get('/profile/{id}',  [ProfileController::class, 'show']);
$router->post('/match/submit', [MatchController::class,   'submit']);
```

**GET** = l'utilisateur veut voir quelque chose (afficher une page)  
**POST** = l'utilisateur envoie des données (formulaire soumis)

---

## 8. Le système de middleware

### C'est quoi un middleware ?

Un middleware est du code qui s'exécute **entre** la réception de la requête et l'appel du controller.  
C'est un "filtre" que la requête doit traverser avant d'arriver à destination.

```
Requête → [Middleware] → Controller → View → Réponse
```

Sans middleware, il faudrait écrire la même vérification dans **chaque** méthode de chaque controller :
```php
// Sans middleware — répété partout, intenable
public function show(): void
{
    if (!Session::get('user_id')) {
        Response::redirect('/login');  // copié-collé dans 10 méthodes
    }
    // ... logique du controller
}
```

Avec middleware, on déclare une fois sur la route et c'est automatique.

---

### Les middlewares de VFoot Arena

| Fichier | Nom | Rôle |
|---|---|---|
| `AuthMiddleware.php` | `auth` | Bloque si l'utilisateur **n'est pas connecté** → redirige vers `/login` |
| `GuestMiddleware.php` | `guest` | Bloque si l'utilisateur **est déjà connecté** → redirige vers `/cups` |

---

### Comment les déclarer sur une route

Le 3ème paramètre de `get()` et `post()` est le tableau des middlewares à appliquer :

```php
// Routes protégées — réservées aux utilisateurs connectés
$router->get('/cups',           [CupController::class,     'index'],     ['auth']);
$router->get('/profile/{id}',   [ProfileController::class, 'show'],      ['auth']);
$router->post('/match/submit',  [MatchController::class,   'submit'],    ['auth']);

// Routes invités — interdites si déjà connecté
$router->get('/login',          [AuthController::class,    'showLogin'], ['guest']);
$router->post('/login',         [AuthController::class,    'login'],     ['guest']);
$router->get('/register',       [AuthController::class,    'showRegister'], ['guest']);

// Routes publiques — accessibles par tous, pas de middleware
$router->get('/',               [CupController::class,     'home'],      []);
```

---

### Comment le Router exécute les middlewares

Dans `Router::dispatch()`, juste avant d'appeler le controller :

```php
foreach ($route['middleware'] as $name) {
    // 'auth'  → AuthMiddleware
    // 'guest' → GuestMiddleware
    $class = $this->middlewareMap[$name];

    $middleware = new $class();
    $middleware->handle();
    // Si handle() redirige → Response::redirect() appelle exit
    // L'exécution s'arrête ici, le controller n'est jamais appelé
}
// Si on arrive ici, tous les middlewares sont passés → on appelle le controller
```

---

### Le flux avec middleware

```
GET /cups (utilisateur non connecté)
    │
    ▼
Router::dispatch()
    │
    ├─ Trouve la route /cups → middleware: ['auth']
    │
    ▼
AuthMiddleware::handle()
    │
    ├─ Session::get('user_id') → null (pas connecté)
    │
    ▼
Response::redirect('/login')  ←  exit  ←  le controller n'est JAMAIS appelé


GET /cups (utilisateur connecté)
    │
    ▼
Router::dispatch()
    │
    ├─ Trouve la route /cups → middleware: ['auth']
    │
    ▼
AuthMiddleware::handle()
    │
    ├─ Session::get('user_id') → 42 (connecté ✓)
    │
    ▼
CupController::index()  ←  le controller est appelé normalement
```

---

### `app/Core/Session.php` — classe dépendante des middlewares

Les middlewares utilisent `Session` pour lire si l'utilisateur est connecté.

| Méthode | Rôle |
|---|---|
| `Session::start()` | Démarre la session PHP si pas encore démarrée |
| `Session::get('user_id')` | Lit une valeur — retourne `null` si absente |
| `Session::set('user_id', 42)` | Écrit une valeur |
| `Session::remove('user_id')` | Supprime une clé |
| `Session::destroy()` | Détruit toute la session (déconnexion) |
| `Session::flash('error', '...')` | Message à usage unique (affiché une fois après redirection) |
| `Session::getFlash('error')` | Lit et supprime le message flash |

---

### `app/Core/Response.php` — classe dépendante des middlewares

Les middlewares utilisent `Response::redirect()` pour couper la requête.

| Méthode | Rôle |
|---|---|
| `Response::redirect('/login')` | Envoie un header HTTP Location + `exit` |
| `Response::json($data, 200)` | Envoie du JSON (pour les requêtes AJAX) |
| `Response::abort(403, 'Interdit')` | Envoie un code d'erreur HTTP + arrête |

> **Pourquoi `exit` après une redirection ?**  
> En PHP, `header('Location: ...')` seul ne stoppe pas l'exécution.  
> Sans `exit`, le code continue de tourner après la redirection — bug courant.  
> `Response::redirect()` appelle toujours `exit` pour garantir l'arrêt.

---

## 9. Gestion des variables d'environnement (.env)

### Le problème

Les identifiants de connexion à la base de données (username, password) ne doivent **jamais** être écrits directement dans le code PHP, et encore moins être poussés sur GitHub.  
Si quelqu'un clone le repo, il ne doit pas voir tes credentials.

### La solution standard : le fichier `.env`

Un fichier `.env` (environment) contient des variables propres à chaque machine.  
Chaque développeur a son propre `.env` local — il n'est jamais partagé.

```
# .env  (jamais commit)
DB_HOST=localhost
DB_NAME=efootball_tournament
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4
```

### Pourquoi pas un package Composer ?

La solution standard en PHP utilise le package `vlucas/phpdotenv`.  
Mais ce projet est développé **sans packages externes**.  
On a donc écrit notre propre lecteur `.env` from scratch en pur PHP.

### Notre solution : `app/Core/Env.php`

```php
class Env
{
    public static function load(string $path): void
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignore les commentaires
            if (str_starts_with(trim($line), '#')) continue;

            // Sépare KEY=VALUE
            [$key, $value] = explode('=', $line, 2);

            // Stocke dans $_ENV pour accès global
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }

    public static function get(string $key, string $default = ''): string
    {
        return $_ENV[$key] ?? $default;
    }
}
```

**Comment ça fonctionne ligne par ligne :**
- `file()` lit le fichier `.env` et retourne un tableau de lignes
- `FILE_IGNORE_NEW_LINES` — supprime les `\n` en fin de ligne
- `FILE_SKIP_EMPTY_LINES` — ignore les lignes vides
- `str_starts_with(..., '#')` — ignore les commentaires
- `explode('=', $line, 2)` — coupe à la première `=` uniquement (la valeur peut contenir des `=`)
- `$_ENV[$key] = $value` — stocke la variable pour PHP
- `putenv(...)` — stocke aussi dans l'environnement système

### Ordre de chargement dans `index.php`

```
index.php
  1. Env::load('.env')         ← lit le fichier, remplit $_ENV
  2. autoload.php              ← charge les classes
  3. config/database.php       ← lit $_ENV via Env::get()
  4. Database::getInstance()   ← utilise ces valeurs pour PDO
```

### Fichiers liés au .env

| Fichier | Commité ? | Rôle |
|---|---|---|
| `.env` | Non | Tes vrais credentials (local uniquement) |
| `.env.example` | Oui | Modèle vide montrant les clés attendues |
| `app/Core/Env.php` | Oui | Le lecteur .env fait maison |
| `config/database.php` | Non | Lit depuis $_ENV, contient `Env::get()` |

### Ce que chaque membre de l'équipe doit faire

Après avoir cloné le repo :
```bash
# Copier le modèle
cp .env.example .env

# Puis ouvrir .env et remplir avec ses propres credentials
```

---

## 9. Fonctionnalités du projet

### Authentification
- Inscription avec email + username + mot de passe
- Connexion / déconnexion
- Protection des pages (redirecton si non connecté)

### Cups
- Voir toutes les cups publiques ouvertes
- Rejoindre une cup publique ou privée (avec code)
- Voir le détail d'une cup (groupes, classement, bracket)

### Matchs
- Voir ses matchs à jouer
- Soumettre un score (avec option upload screenshot)
- Système de validation : accord automatique, litige si désaccord

### Profil joueur
- Page publique avec stats par phase (groupes / knockout)
- Trophées remportés

### Feature IA
- Extraction automatique du score depuis une capture d'écran eFootball via API de vision par ordinateur

---

## 10. Équipe et répartition

| Membre | Langage | Interface |
|---|---|---|
| Membre 1 (ce projet) | PHP | Interface web — joueur |
| Membre 2 | Java | Console — organisateur |
| Membre 3 | C++ | Console — administrateur |

Les trois partagent la même base MySQL `efootball_tournament`.

---

## 11. Lancer le projet en local

### Prérequis
- XAMPP installé (Apache + MySQL + PHP)

### Étapes

**1. Placer le projet**
```
Copier le dossier vfoot/ dans : C:/xampp/htdocs/vfoot/
```

**2. Créer la base de données**
- Ouvrir phpMyAdmin : `http://localhost/phpmyadmin`
- Créer une base `efootball_tournament`
- Importer le fichier `efootball_tournament_v2.sql`

**3. Configurer les variables d'environnement**
```bash
# Copier le modèle
cp .env.example .env
```
Ouvrir `.env` et remplir avec ses credentials :
```
DB_HOST=localhost
DB_NAME=efootball_tournament
DB_USER=root
DB_PASS=        ← vide par défaut sur XAMPP
DB_CHARSET=utf8mb4
```

**4. Démarrer XAMPP**
- Lancer Apache et MySQL depuis le panneau XAMPP

**5. Ouvrir dans le navigateur**
```
http://localhost/vfoot/
```

---

*VFoot Arena — Projet académique EHEI Oujda 2025/2026*
