cmake_minimum_required(VERSION 3.21)
project(EFootApp VERSION 1.0 LANGUAGES CXX)

set(CMAKE_CXX_STANDARD 17)
set(CMAKE_CXX_STANDARD_REQUIRED ON)
set(CMAKE_AUTOMOC ON)

find_package(Qt6 REQUIRED COMPONENTS Core Gui Widgets Quick Qml QuickControls2 Sql)
qt_standard_project_setup()

# ── C++ sources ────────────────────────────────────────────────────────────────
qt_add_executable(EFootApp
    main.cpp

    core/DatabaseManager.h    core/DatabaseManager.cpp
    core/SqlQueryModel.h      core/SqlQueryModel.cpp
    core/BaseViewModel.h      core/BaseViewModel.cpp

    model/User.h
    model/AuthService.h       model/AuthService.cpp

    repository/UserRepository.h     repository/UserRepository.cpp
    repository/CupRepository.h      repository/CupRepository.cpp
    repository/DashboardRepository.h repository/DashboardRepository.cpp
    repository/MatchRepository.h    repository/MatchRepository.cpp
    repository/TrophyRepository.h   repository/TrophyRepository.cpp
    repository/GroupeRepository.h   repository/GroupeRepository.cpp

    viewmodel/LoginViewModel.h      viewmodel/LoginViewModel.cpp
    viewmodel/AppViewModel.h        viewmodel/AppViewModel.cpp
    viewmodel/DashboardViewModel.h  viewmodel/DashboardViewModel.cpp
    viewmodel/UsersViewModel.h      viewmodel/UsersViewModel.cpp
    viewmodel/CupsViewModel.h       viewmodel/CupsViewModel.cpp
    viewmodel/GroupesViewModel.h    viewmodel/GroupesViewModel.cpp
    viewmodel/MatchesViewModel.h    viewmodel/MatchesViewModel.cpp
    viewmodel/TrophiesViewModel.h   viewmodel/TrophiesViewModel.cpp
)

# ── QML module ─────────────────────────────────────────────────────────────────
# URI = EFoot  →  entry point at  qrc:/EFoot/view/main.qml
# All types within the module are auto-accessible to each other.
qt_add_qml_module(EFootApp
    URI EFoot
    VERSION 1.0
    QML_FILES
        view/main.qml
        view/LoginPage.qml
        view/DashboardShell.qml
        view/DashboardHomePage.qml
        view/UsersPage.qml
        view/CupsPage.qml
        view/GroupesPage.qml
        view/MatchesPage.qml
        view/TrophiesPage.qml
        view/SidebarPanel.qml
        view/NavButton.qml
        view/DataTable.qml
)

target_include_directories(EFootApp PRIVATE
    core
    model
    repository
    viewmodel
)

target_link_libraries(EFootApp PRIVATE
    Qt6::Core
    Qt6::Gui
    Qt6::Widgets
    Qt6::Quick
    Qt6::Qml
    Qt6::QuickControls2
    Qt6::Sql
)
