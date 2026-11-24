# 🌐 VFoot Arena — Player web app (PHP)

> 👤 **My part of the project.** I designed and built this application.

Web interface for players, built on a hand-rolled MVC framework (no Laravel or Symfony).

## Features
- Register / login (bcrypt, session-based auth, auth middleware)
- Browse cups, join a cup directly or with an invite code
- View your matches and submit scores (auto-confirm or dispute)
- Profile page and trophies

## Stack
PHP 8.2 (8.1+ required) · PSR-7 / PSR-15 middleware · PHP-DI (PSR-11) · League Route · League Plates · vlucas/phpdotenv · Repository + Data Mapper (no ORM)

## Structure
```
php/
├── public/index.php     Single entry point (front controller)
├── config/              routes.php, DI definitions
├── src/
│   ├── Controllers/     Auth, Cup, Matche, Profile, Home
│   ├── Entities/        User, Cup, ...
│   ├── Mappers/         DB row → entity
│   ├── Repositories/    SQL access
│   ├── Framework/       Router glue, middleware, template renderer
│   └── bootstrap.php    Loads .env, builds the container
└── views/               Plates templates
```

## Routes
| Method | Path | Auth |
|---|---|---|
| GET/POST | `/login`, `/register` | no |
| GET | `/logout` | no |
| GET | `/cups`, `/cups/{id}` | yes |
| GET/POST | `/cups/join-code` | yes |
| POST | `/cups/{id}/join` | yes |
| GET | `/matches`, `/matches/{id}` | yes |
| POST | `/matches/{id}/score` | yes |
| GET | `/profile`, `/profile/trophees` | yes |
