#include "UserRepository.h"
#include "DatabaseManager.h"
#include <QSqlQuery>
#include <QSqlError>
#include <QDebug>

QString UserRepository::lastError() const { return m_lastError; }

bool UserRepository::execWrite(const QString& sql, const QVariantList& bindings) {
    QSqlQuery q(DatabaseManager::instance().database());
    q.prepare(sql);
    for (const QVariant& v : bindings)
        q.addBindValue(v);
    if (q.exec()) return true;
    m_lastError = q.lastError().text();
    qWarning() << "[UserRepository]" << m_lastError;
    return false;
}

void UserRepository::loadAll(SqlQueryModel* model) {
    const QString roleCol = DatabaseManager::instance().detectRoleColumn();
    const QString sql = QString(
        "SELECT u.id_user, u.username, u.email, r.%1 AS `Rôle`, "
        "CASE WHEN u.is_banned = 1 THEN 'Oui' ELSE 'Non' END AS `Banni` "
        "FROM users u "
        "INNER JOIN role r ON u.id_role = r.id_role "
        "ORDER BY u.created_at DESC"
    ).arg(roleCol);
    model->refresh(sql, DatabaseManager::instance().database());
}

bool UserRepository::isBanned(int userId) const {
    QSqlQuery q(DatabaseManager::instance().database());
    q.prepare(QStringLiteral("SELECT is_banned FROM users WHERE id_user = ?"));
    q.addBindValue(userId);
    if (!q.exec()) qWarning() << "[UserRepository]" << q.lastError().text();
    return q.next() && q.value(0).toBool();
}

bool UserRepository::changeName(int userId, const QString& name) {
    return execWrite(
        QStringLiteral("UPDATE users SET username = ? WHERE id_user = ?"),
        {name, userId});
}

bool UserRepository::changeEmail(int userId, const QString& email) {
    return execWrite(
        QStringLiteral("UPDATE users SET email = ? WHERE id_user = ?"),
        {email, userId});
}

bool UserRepository::toggleBan(int userId, bool ban) {
    return execWrite(
        QStringLiteral("UPDATE users SET is_banned = ? WHERE id_user = ?"),
        {ban, userId});
}

bool UserRepository::deleteUser(int userId) {
    return execWrite(
        QStringLiteral("DELETE FROM users WHERE id_user = ?"),
        {userId});
}
