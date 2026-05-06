<?php

class Router
{
    // Liste de toutes les routes enregistrées
    private array $routes = [];

    // Correspondance nom court → classe middleware
    private array $middlewareMap = [
        'auth'  => AuthMiddleware::class,
        'guest' => GuestMiddleware::class,
    ];

    // ----------------------------------------------------------------
    // Enregistrement des routes
    // ----------------------------------------------------------------

    // Enregistre une route GET (afficher une page)
    public function get(string $path, array $action, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $action, $middleware);
    }

    // Enregistre une route POST (soumettre un formulaire)
    public function post(string $path, array $action, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $action, $middleware);
    }

    // Méthode interne — stocke la route dans le tableau
    private function addRoute(string $method, string $path, array $action, array $middleware): void
    {
        $this->routes[] = [
            'method'     => $method,     // GET ou POST
            'path'       => $path,       // ex: /profile/{id}
            'action'     => $action,     // [NomController::class, 'nomMethode']
            'middleware' => $middleware, // ['auth'], ['guest'], ou []
        ];
    }

    // ----------------------------------------------------------------
    // Dispatch — cœur du routeur
    // ----------------------------------------------------------------

    public function dispatch(): void
    {
        // 1. Lire la méthode HTTP (GET, POST...)
        $method = $_SERVER['REQUEST_METHOD'];

        // 2. Lire l'URI demandée et supprimer les paramètres GET (?foo=bar)
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // 3. Retirer le sous-dossier /vfoot/public si le projet n'est pas à la racine
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        // 4. URI vide → page d'accueil
        $uri = $uri ?: '/';

        // 5. Chercher une route qui correspond
        foreach ($this->routes as $route) {

            // Mauvaise méthode HTTP → on passe
            if ($route['method'] !== $method) {
                continue;
            }

            // Tenter de faire correspondre l'URI avec le chemin de la route
            // matchPath() retourne les paramètres extraits, ou null si pas de correspondance
            $params = $this->matchPath($route['path'], $uri);

            if ($params === null) {
                continue;
            }

            // --- Route trouvée ---

            // 6. Exécuter les middlewares dans l'ordre
            foreach ($route['middleware'] as $name) {
                $class = $this->middlewareMap[$name] ?? null;
                if ($class) {
                    $middleware = new $class();
                    $middleware->handle();
                    // Si le middleware redirige, handle() appelle exit — on n'arrive pas ici
                }
            }

            // 7. Instancier le controller et appeler la méthode
            [$controllerClass, $methodName] = $route['action'];
            $controller = new $controllerClass();
            $controller->$methodName($params);

            return; // Route traitée, on s'arrête
        }

        // 8. Aucune route trouvée → 404
        http_response_code(404);
        echo '<h1>404 — Page non trouvée</h1>';
    }

    // ----------------------------------------------------------------
    // Correspondance de chemin + extraction des paramètres dynamiques
    // ----------------------------------------------------------------

    // Exemple : routePath = /profile/{id}  ,  uri = /profile/42
    //           → retourne ['id' => '42']
    //
    // Exemple : routePath = /cups  ,  uri = /cups
    //           → retourne [] (tableau vide = pas de paramètres)
    //
    // Exemple : routePath = /cups  ,  uri = /login
    //           → retourne null (pas de correspondance)

    private function matchPath(string $routePath, string $uri): ?array
    {
        // Étape 1 : extraire les noms des paramètres dynamiques
        // ex: /profile/{id}  → ['id']
        preg_match_all('/\{([a-z_]+)\}/', $routePath, $paramNames);

        // Étape 2 : transformer le chemin en expression régulière
        // ex: /profile/{id}  → #^/profile/([^/]+)$#
        $pattern = preg_replace('/\{[a-z_]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        // Étape 3 : tester l'URI contre le pattern
        if (!preg_match($pattern, $uri, $matches)) {
            return null; // Pas de correspondance
        }

        // Étape 4 : associer les noms aux valeurs capturées
        // $matches[0] = l'URI complète, $matches[1], [2]... = les groupes capturés
        $params = [];
        foreach ($paramNames[1] as $index => $name) {
            $params[$name] = $matches[$index + 1];
        }

        return $params;
    }
}
