# 🛡️ VFoot Arena — Admin desktop app (C++ / Qt 6)

Desktop administration app built with **Qt 6 / QML** using the **MVVM** pattern.

## Features
- Admin login (only accounts with the `admin` role)
- Dashboard with platform statistics
- Users: list, ban and unban players
- Cups, groups and matches supervision
- Trophies management

## Structure
```
admin/
├── main.cpp            Wires up services, ViewModels and the QML engine
├── core/               DatabaseManager, BaseViewModel, SqlQueryModel
├── model/              AuthService, User
├── viewmodel/          One ViewModel per page (Login, Dashboard, Users, Cups, ...)
└── view/               QML pages (LoginPage, DashboardShell, DataTable, ...)
```

## Requirements
- CMake 3.21+, C++17 compiler (MSVC 2022 or MinGW)
- Qt 6 with modules: Core, Gui, Widgets, Quick, Qml, QuickControls2, **Sql**
- Qt MySQL driver (`qsqlmysql`) and the MySQL client library (`libmysql.dll`) next to the executable

## Build
```bash
cd admin
cmake -S . -B build_win -DCMAKE_PREFIX_PATH="C:/Qt/6.x.x/msvc2022_64"
cmake --build build_win --config Release
```
Or open `CMakeLists.txt` in Qt Creator or VS Code (CMake Tools).

## Database configuration
The connection is read from environment variables (defaults = local XAMPP):

| Variable | Default |
|---|---|
| `DB_HOST` | `127.0.0.1` |
| `DB_PORT` | `3306` |
| `DB_NAME` | `efootball_tournament` |
| `DB_USERNAME` | `root` |
| `DB_PASSWORD` | *(empty)* |

```powershell
$env:DB_PASSWORD = "your-password"; .\build_win\Release\EFootApp.exe
```

> Build folders (`build/`, `build_win/`) are git-ignored.
