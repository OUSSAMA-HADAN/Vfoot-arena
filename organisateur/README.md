# 🗂️ VFoot Arena — Organizer console (Java)

> 👥 Built by a teammate in the project group, not by me.

Console application for tournament organizers, built with the **MVP** pattern (Model / View / Presenter).

## Features
- Organizer login (bcrypt, compatible with PHP `$2y$` hashes)
- Create and manage cups
- Group stage: standings
- Knockout stage: automatic bracket generation and advancement
- Dispute resolution: settle score conflicts between two players

## Structure
```
src/main/java/vfoot/
├── Main.java
├── config/Database.java      JDBC connection (reads config.properties / env vars)
├── model/                    Cup, Match, Organisateur, Standing
├── repository/               Auth, Cup, Match, Bracket (SQL)
├── presenter/                Auth, Cup, Eliminatoire, Litige
└── view/ConsoleView.java     Console I/O
```
