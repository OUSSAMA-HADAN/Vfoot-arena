# ⚽ VFoot Arena

> eFootball tournament management platform: group stage, knockout bracket, score submission and dispute resolution.
> *Projet de synthèse — 3ème année Génie Informatique, EHEI Oujda.*

VFoot Arena is a group project made of three applications that share one MySQL database. Each application serves a different type of user:

| Module | Folder | Stack | Who uses it | Built by |
|---|---|---|---|---|
| 🌐 **Player web app** | [`php/`](php/) | PHP 8.2, MVC built from scratch | Players: register, join cups, submit scores, view trophies | **Me** ([@OUSSAMA-HADAN](https://github.com/OUSSAMA-HADAN)) |
| 🗂️ **Organizer console** | [`organisateur/`](organisateur/) | Java 21, Maven, MVP pattern, JDBC | Organizers: create cups, generate groups and brackets, resolve disputes | Teammate |
| 🛡️ **Admin desktop app** | [`admin/`](admin/) | C++17, Qt 6 / QML, MVVM | Admins: users, bans, cups, matches, trophies | Teammate |
| 🗄️ **Database** | [`database/`](database/) | MySQL / MariaDB | Shared by all three apps | Shared |

---

## How a tournament works

1. **Group stage:** players are split into groups of 4–5 and play round-robin (win 3 pts, draw 1, loss 0). The top 2 of each group qualify.
2. **Knockout stage:** single elimination, generated automatically (round of 16 → final).
3. **Scores:** both players submit the result. If the two submissions match, the score is confirmed. If they differ, a **dispute** is opened and the organizer decides.

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

## My contribution

My main task was the **player web application** ([`php/`](php/)), the part of the platform used by its main user: **the player**.

I built it in **PHP 8.2** on an MVC framework I wrote from scratch, without Laravel or Symfony.

### Features
- **Authentication:** register and login with bcrypt-hashed passwords, sessions, and middleware that protects private pages
- **Cups:** browse tournaments, see their details, join one directly or with an invite code
- **Matches:** list my matches and submit scores. Matching submissions are confirmed automatically; differing ones become a dispute for the organizer.
- **Profile:** player profile and won trophies

### Architecture
```
Request → public/index.php → Middleware (auth) → Router → Controller
                                                            │
                                           Repository → Mapper → Entity
                                                            │
                                                  Plates view → Response
```
- **Front controller:** every request goes through a single entry point, `public/index.php`
- **PSR-7 / PSR-15:** standard HTTP request and response objects, and a middleware pipeline
- **Dependency injection** with PHP-DI (PSR-11)
- **Routing** with League Route, **templates** with League Plates
- **Repository + Data Mapper:** SQL stays in repositories, mappers turn database rows into entities, and there is no ORM
- **Configuration** from an environment file, so no credentials live in the code

---

## What I learned

- **How a web framework works inside:** building routing, middleware, dependency injection and templating myself showed me what frameworks like Laravel do for you.
- **Following PHP standards (PSR):** using PSR-7, PSR-11 and PSR-15 made my code work with third-party libraries instead of being tied to my own classes.
- **Separating responsibilities:** controllers handle HTTP, repositories handle SQL, and mappers and entities handle data. This made features easier to add and bugs easier to find.
- **Web security basics:** password hashing with bcrypt, session-based authentication, prepared statements against SQL injection, and keeping secrets out of the source code.
- **Designing around business rules:** turning the tournament rules (double score submission, disputes, group qualification) into data and code.
- **Teamwork on a shared database:** three apps in three languages, built by different people, all depending on the same schema. Changes had to be agreed on and coordinated through Git.

---

## Other parts of the project (teammates)

The **organizer console (Java)** and the **admin desktop app (C++/Qt)** were built by **my teammates** in the project group. They are described here so the whole platform makes sense, but they are not my work.

- **Organizer console:** a Java console app in the MVP pattern. Organizers create cups, follow group standings, generate the knockout bracket and resolve score disputes.
- **Admin desktop app:** a Qt 6 / QML desktop app in the MVVM pattern. Admins manage users (ban and unban), supervise cups, groups and matches, and manage trophies from a dashboard.
