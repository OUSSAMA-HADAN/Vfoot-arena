# 🛡️ VFoot Arena — Admin desktop app (C++ / Qt 6)

> 👥 Built by a teammate in the project group, not by me.

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
