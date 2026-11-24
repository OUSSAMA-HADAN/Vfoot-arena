#include "AuthService.h"
#include "DatabaseManager.h"
#include <QSqlQuery>
#include <QSqlError>
#include <QDebug>

extern "C" {
#include "bcrypt.h"
}

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

    // Récupère l'utilisateur admin sans comparer le mot de passe en SQL.
    // La vérification BCrypt se fait côté C++ après la requête.
    const QString roleCol = db.detectRoleColumn();
    const QString sql = QString(
        "SELECT u.id_user, u.username, u.email, u.password AS hash, "
        "       r.%1 AS role, u.is_banned "
        "FROM   users u "
        "INNER JOIN role r ON u.id_role = r.id_role "
        "WHERE  u.email = ? AND r.nom = 'admin' AND u.is_banned = false "
        "LIMIT  1"
    ).arg(roleCol);

    QSqlQuery q(db.database());
    q.prepare(sql);
    q.addBindValue(email.trimmed());

    if (!q.exec() || !q.next()) {
        m_lastError = QStringLiteral("Email ou mot de passe incorrect.");
        return false;
    }

    // Vérification BCrypt — PHP stocke $2y$, libbcrypt attend $2a$ (identiques algorithmiquement).
    QString hash = q.value("hash").toString();
    hash.replace("$2y$", "$2a$");

    QByteArray passBytes = password.toUtf8();
    QByteArray hashBytes = hash.toUtf8();

    if (bcrypt_checkpw(passBytes.constData(), hashBytes.constData()) != 0) {
        m_lastError = QStringLiteral("Email ou mot de passe incorrect.");
        return false;
    }

    outUser.idUser   = q.value("id_user").toInt();
    outUser.username = q.value("username").toString();
    outUser.email    = q.value("email").toString();
    outUser.role     = q.value("role").toString();
    outUser.isBanned = false;
    return true;
}

QString AuthService::lastError() const {
    return m_lastError;
}
