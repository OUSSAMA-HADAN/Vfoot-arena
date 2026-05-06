#include <QApplication>
#include <QQmlApplicationEngine>
#include <QQmlContext>
#include <QtQuickControls2/QQuickStyle>

#include "DatabaseManager.h"
#include "SqlQueryModel.h"
#include "AuthService.h"
#include "LoginViewModel.h"
#include "AppViewModel.h"
#include "DashboardViewModel.h"
#include "UsersViewModel.h"
#include "CupsViewModel.h"
#include "GroupesViewModel.h"
#include "MatchesViewModel.h"
#include "TrophiesViewModel.h"

int main(int argc, char* argv[]) {


    QQuickStyle::setStyle(QStringLiteral("Basic"));

    QApplication app(argc, argv);
    app.setApplicationName(QStringLiteral("eFoot Admin"));
    app.setApplicationVersion(QStringLiteral("1.0"));


    AuthService authService;
    authService.connectDatabase(
        QStringLiteral("127.0.0.1"), 3306,
        QStringLiteral("efootball_tournament"),
        QStringLiteral("root"), QStringLiteral("")
    );


    AppViewModel       appVM;
    LoginViewModel     loginVM(&authService);
    DashboardViewModel dashVM;
    UsersViewModel     usersVM;
    CupsViewModel      cupsVM;
    GroupesViewModel   groupesVM;
    MatchesViewModel   matchesVM;
    TrophiesViewModel  trophiesVM;


    QObject::connect(&loginVM, &LoginViewModel::loginSuccess,
                     &appVM,   &AppViewModel::onLoginSuccess);


    QObject::connect(&appVM, &AppViewModel::authChanged, &appVM, [&]() {
        if (appVM.loggedIn()) {
            dashVM.loadStats();
            dashVM.loadCups();
            usersVM.loadUsers();
            cupsVM.loadCups();
            matchesVM.loadMatches();
            trophiesVM.loadTrophies();
        }
    });


    qmlRegisterType<SqlQueryModel>("EFoot", 1, 0, "SqlQueryModel");


    QQmlApplicationEngine engine;
    auto* ctx = engine.rootContext();

    ctx->setContextProperty(QStringLiteral("appVM"),      &appVM);
    ctx->setContextProperty(QStringLiteral("loginVM"),    &loginVM);
    ctx->setContextProperty(QStringLiteral("dashVM"),     &dashVM);
    ctx->setContextProperty(QStringLiteral("usersVM"),    &usersVM);
    ctx->setContextProperty(QStringLiteral("cupsVM"),     &cupsVM);
    ctx->setContextProperty(QStringLiteral("groupesVM"),  &groupesVM);
    ctx->setContextProperty(QStringLiteral("matchesVM"),  &matchesVM);
    ctx->setContextProperty(QStringLiteral("trophiesVM"), &trophiesVM);

    const QUrl mainUrl(QStringLiteral("qrc:/EFoot/view/main.qml"));
    QObject::connect(&engine, &QQmlApplicationEngine::objectCreated,
                     &app, [mainUrl](QObject* obj, const QUrl& url) {
        if (!obj && url == mainUrl)
            QCoreApplication::exit(-1);
    }, Qt::QueuedConnection);

    engine.load(mainUrl);
    return app.exec();
}
