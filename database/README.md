# 🗄️ VFoot Arena — Database

Shared MySQL / MariaDB database `efootball_tournament` (utf8mb4), used by all three apps.

## Files
| File | Purpose |
|---|---|
| `efootball_tournament_v2.sql` | Full schema (creates the database and all tables) |
| `seed.sql` | Demo data: 8 users, 3 cups, trophies |
| `seed.php` | Same seed, generated from PHP (reads `php/.env`) |

## Tables
`role` · `users` · `cup` · `groupe` · `matche` · `submission` · `resultat` · `participe` · `est_classe_dans` · `trophee` · `remporte`

- `role`: `1 = player`, `2 = admin`
- `submission`: each player's reported score. Two matching submissions produce a `resultat`.
- `matche.avance_vers`: next knockout match the winner advances to
