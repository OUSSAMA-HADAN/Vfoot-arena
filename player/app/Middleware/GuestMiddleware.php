<?php

// Middleware GUEST — vérifie que l'utilisateur N'est PAS connecté
// Si déjà connecté → redirige vers /cups (pas besoin de se reconnecter)
// S'applique aux pages publiques : /login, /register

class GuestMiddleware
{
    public function handle(): void
    {
        Session::start();

        if (Session::get('user_id')) {
            Response::redirect('/cups');
        }
    }
}
