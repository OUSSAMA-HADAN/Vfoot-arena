#include "AuthService.h"
#include "DatabaseManager.h"
#include <QSqlQuery>
#include <QSqlRecord>
#include <QSqlError>
#include <QDebug>

AuthService::AuthService(QObject* parent) : QObject(parent) {}

bool AuthService::connectDatabase(const QString& host, int port,
                                   const QString& dbName,
                                   const QString& user,
                                   const QString& password) {
    auto& db = DatabaseManager::instance();
    if (!db.connect(host, port, dbName, user, password)) {
        m_lastError = db.lastError();
        return false;
    }
    return true;
}

bool AuthService::authenticate(const QString& email,
                                const QString& password,
                                User&          outUser) {
    auto& db = DatabaseManager::instance();
    if (!db.isConnected()) {
        m_lastError = QStringLiteral("Non connecté à la base de données.");
        return false;
    }

    const QString roleCol = db.detectRoleColumn();

    QString e = email.trimmed();    e.replace("'", "''");
    QString p = password;           p.replace("'", "''");

    const QString sql = QString(
        "SELECT u.id_user, u.username, u.email, r.%1 AS role, u.is_banned "
        "FROM users u "
        "INNER JOIN role r ON u.id_role = r.id_role "
        "WHERE u.email = '%2' AND u.password = '%3' "
        "LIMIT 1"
    ).arg(roleCol, e, p);

    QSqlQuery q = db.exec(sql);

    if (!q.next()) {
        m_lastError = QStringLiteral("Email ou mot de passe incorrect.");
        return false;
    }

    if (q.value(4).toBool()) {
        m_lastError = QStringLiteral("Ce compte est banni.");
        return false;
    }

    outUser.idUser   = q.value(0).toInt();
    outUser.username = q.value(1).toString();
    outUser.email    = q.value(2).toString();
    outUser.role     = q.value(3).toString();
    outUser.isBanned = q.value(4).toBool();
    return true;
}

QString AuthService::lastError() const {
    return m_lastError;
}
