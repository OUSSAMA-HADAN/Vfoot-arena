#include "AuthService.h"
#include "DatabaseManager.h"
#include <QCryptographicHash>
#include <QSqlQuery>
#include <QSqlError>

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
    const QString sql = QString(
        "SELECT u.id_user, u.username, u.email, r.%1 AS role, u.is_banned "
        "FROM users u "
        "INNER JOIN role r ON u.id_role = r.id_role "
        "WHERE u.email = ? AND u.password = ? "
        "LIMIT 1"
    ).arg(roleCol);

    const QString hashedPassword = QString::fromLatin1(
        QCryptographicHash::hash(password.toUtf8(), QCryptographicHash::Sha256).toHex()
    );

    QSqlQuery q(db.database());
    q.prepare(sql);
    q.addBindValue(email.trimmed());
    q.addBindValue(hashedPassword);
    if (!q.exec()) {
        m_lastError = q.lastError().text();
        return false;
    }

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
