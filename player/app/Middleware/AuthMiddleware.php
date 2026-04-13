<?php

// Middleware AUTH — vérifie que l'utilisateur est connecté
// Si non connecté → redirige vers /login
// S'applique à toutes les pages privées : /cups, /match, /profile...

class AuthMiddleware
{
    public function handle(): void
    {
        Session::start();

        if (!Session::get('user_id')) {
            Response::redirect('/login');
        }
    }
}
