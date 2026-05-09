#include <QApplication>
#include <QQmlApplicationEngine>
#include <QQmlContext>
#include <QtQuickControls2/QQuickStyle>

#include "AuthService.h"
#include "LoginViewModel.h"
#include "AppViewModel.h"
#include "DashboardViewModel.h"
#include "UsersViewModel.h"
#include "CupsViewModel.h"
#include "MatchesViewModel.h"
#include "TrophiesViewModel.h"
#include "GroupesViewModel.h"

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
    MatchesViewModel   matchesVM;
    TrophiesViewModel  trophiesVM;
    GroupesViewModel   groupesVM;

    QObject::connect(&loginVM, &LoginViewModel::loginSuccess,
                     &appVM,   &AppViewModel::onLoginSuccess);

    QObject::connect(&appVM, &AppViewModel::authChanged, &appVM, [&]() {
        if (appVM.loggedIn()) {
            dashVM.loadStats();
            dashVM.loadCups();
        }
    });

    QQmlApplicationEngine engine;

    engine.rootContext()->setContextProperty(QStringLiteral("appVM"),      &appVM);
    engine.rootContext()->setContextProperty(QStringLiteral("loginVM"),    &loginVM);
    engine.rootContext()->setContextProperty(QStringLiteral("dashVM"),     &dashVM);
    engine.rootContext()->setContextProperty(QStringLiteral("usersVM"),    &usersVM);
    engine.rootContext()->setContextProperty(QStringLiteral("cupsVM"),     &cupsVM);
    engine.rootContext()->setContextProperty(QStringLiteral("matchesVM"),  &matchesVM);
    engine.rootContext()->setContextProperty(QStringLiteral("trophiesVM"), &trophiesVM);
    engine.rootContext()->setContextProperty(QStringLiteral("groupesVM"),  &groupesVM);

    const QUrl mainUrl(QStringLiteral("qrc:/EFoot/view/main.qml"));
    QObject::connect(&engine, &QQmlApplicationEngine::objectCreated,
                     &app, [mainUrl](QObject* obj, const QUrl& url) {
        if (!obj && url == mainUrl)
            QCoreApplication::exit(-1);
    }, Qt::QueuedConnection);

    engine.load(mainUrl);
    return app.exec();
}
