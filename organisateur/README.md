# 🗂️ VFoot Arena — Organizer console (Java)

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

## Requirements
Java 21 · Maven 3.9+ · MySQL with the `efootball_tournament` database

## Configuration
```bash
cp src/main/resources/config.properties.example src/main/resources/config.properties
# edit db.url / db.user / db.password
```
Environment variables `DB_URL`, `DB_USER` and `DB_PASSWORD` override the file.
`config.properties` is git-ignored.

## Build and run
```bash
mvn package
java -cp "target/organisateur-1.0.0.jar;target/lib/*" vfoot.Main     # Windows
java -cp "target/organisateur-1.0.0.jar:target/lib/*" vfoot.Main     # Linux/macOS
```
