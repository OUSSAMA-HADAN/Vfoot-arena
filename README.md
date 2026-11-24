# ⚽ VFoot Arena

> eFootball tournament management platform: group stage, knockout bracket, score submission and dispute resolution.
> *Projet de synthèse — 3ème année Génie Informatique, EHEI Oujda.*

VFoot Arena is a **monorepo** of three applications that share one MySQL database:

| Module | Folder | Stack | Who uses it |
|---|---|---|---|
| 🌐 **Player web app** | [`php/`](php/) | PHP 8.1+, MVC built from scratch (PSR-7/15, PHP-DI, League Route, Plates) | Players: register, join cups, submit scores, view trophies |
| 🗂️ **Organizer console** | [`organisateur/`](organisateur/) | Java 21, Maven, MVP pattern, JDBC | Organizers: create cups, generate groups and brackets, resolve disputes |
| 🛡️ **Admin desktop app** | [`admin/`](admin/) | C++17, Qt 6 / QML, MVVM | Admins: users, bans, cups, matches, trophies dashboard |
| 🗄️ **Database** | [`database/`](database/) | MySQL / MariaDB | Shared schema + seed data |

---

## How a tournament works

1. **Group stage**: players are split into groups of 4–5 and play round-robin (win 3 pts, draw 1, loss 0). The top 2 of each group qualify.
2. **Knockout stage**: single elimination, generated automatically (round of 16 → final).
3. **Scores**: both players submit the result. If the two submissions match, the score is confirmed. If they differ, a **dispute** is opened and the organizer decides.

```
          ┌──────────────┐   ┌────────────────┐   ┌──────────────┐
 Players ─▶  php/ (web)  │   │ organisateur/  │   │   admin/     │◀─ Admins
          └──────┬───────┘   │  (Java CLI)    │   │  (Qt/QML)    │
                 │           └───────┬────────┘   └──────┬───────┘
                 │                   │  Organizers       │
                 └───────────────────┼───────────────────┘
                                     ▼
                        MySQL · efootball_tournament
```

---

## Repository layout

```
vfoot/
├── php/            Player web app (PHP MVC)
├── organisateur/   Organizer console app (Java / Maven)
├── admin/          Admin desktop app (C++ / Qt 6)
├── database/       Schema (efootball_tournament_v2.sql) + seed data
└── test/           Standalone PHP scripts for testing the DB pipeline
```

---

## Quick start

### 1. Database
```bash
mysql -u root -p < database/efootball_tournament_v2.sql
mysql -u root -p < database/seed.sql          # optional demo data
```
See [`database/README.md`](database/README.md).

### 2. Each app
| App | Guide |
|---|---|
| Web (PHP) | [`php/README.md`](php/README.md) |
| Organizer (Java) | [`organisateur/README.md`](organisateur/README.md) |
| Admin (C++/Qt) | [`admin/README.md`](admin/README.md) |

### Demo accounts (from `seed.sql`, local use only)
| Email | Role |
|---|---|
| `admin@vfoot.com` | admin |
| `org@vfoot.com` | organizer |
| `p1@vfoot.com` … `p6@vfoot.com` | players |

Password for every demo account: `00000000`. **Never reuse these accounts in a real deployment.**

---

## Configuration and secrets 🔒

No credentials are stored in the code. Each app reads them from a local, **git-ignored** file or from environment variables:

| App | Committed template | Your local file (ignored) | Env vars |
|---|---|---|---|
| PHP | `php/.env.example` | `php/.env` | via `.env` |
| Java | `organisateur/src/main/resources/config.properties.example` | `config.properties` | `DB_URL`, `DB_USER`, `DB_PASSWORD` |
| C++ | n/a | n/a | `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USERNAME`, `DB_PASSWORD` |
| test/ | n/a | n/a | `DB_HOST`, `DB_NAME`, `DB_USERNAME`, `DB_PASSWORD` |

If no value is set, each app falls back to the local XAMPP defaults (`root`, empty password).

---

## Branches and versions

| Branch | Purpose |
|---|---|
| `main` | Stable, demo-ready code. Each release is tagged (`v1.0.0`, …). |
| `dev` | Integration branch. Feature branches are merged here first. |
| `feature/<name>` | One branch per feature, merged into `dev` through a pull request. |

Releases are listed on the [Releases](../../releases) page.

---

## License

Academic project. All rights reserved by the authors unless a license file is added.
