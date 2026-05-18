#include "UsersViewModel.h"
#include "DatabaseManager.h"
#include <QSqlQuery>
#include <QSqlError>
#include <QRegularExpression>
#include <QDebug>

UsersViewModel::UsersViewModel(QObject* parent)
    : BaseViewModel(parent)
    , m_usersModel(new SqlQueryModel(this))
{}

SqlQueryModel* UsersViewModel::usersModel()  const { return m_usersModel; }
QString        UsersViewModel::statusMsg()   const { return m_statusMsg; }
bool           UsersViewModel::statusError() const { return m_statusError; }

void UsersViewModel::setStatus(const QString& msg, bool error) {
    m_statusMsg   = msg;
    m_statusError = error;
    emit statusChanged();
}

void UsersViewModel::loadUsers() {
    auto& db = DatabaseManager::instance();
    const QString roleCol = db.detectRoleColumn();
    const QString sql = QString(
        "SELECT u.id_user, u.username, u.email, r.%1 AS `Rôle`, "
        "CASE WHEN u.is_banned = 1 THEN 'Oui' ELSE 'Non' END AS `Banni` "
        "FROM users u "
        "INNER JOIN role r ON u.id_role = r.id_role "
        "ORDER BY u.created_at DESC"
    ).arg(roleCol);
    m_usersModel->refresh(sql, db.database());
}

int UsersViewModel::userIdAtRow(int row) const {
    if (row < 0 || row >= m_usersModel->rowCount()) return -1;
    return m_usersModel->rowData(row).value(0).toInt();
}

QString UsersViewModel::userNameAtRow(int row) const {
    if (row < 0 || row >= m_usersModel->rowCount()) return {};
    return m_usersModel->rowData(row).value(1).toString();
}

QString UsersViewModel::userEmailAtRow(int row) const {
    if (row < 0 || row >= m_usersModel->rowCount()) return {};
    return m_usersModel->rowData(row).value(2).toString();
}

bool UsersViewModel::userIsBannedById(int id) const {
    QSqlQuery q(DatabaseManager::instance().database());
    q.prepare(QStringLiteral("SELECT is_banned FROM users WHERE id_user = ?"));
    q.addBindValue(id);
    if (!q.exec()) qWarning() << q.lastError().text();
    return q.next() && q.value(0).toBool();
}

void UsersViewModel::changeName(int userId, const QString& name) {
    if (name.trimmed().isEmpty()) {
        setStatus(QStringLiteral("Le nom ne peut pas être vide."), true);
        return;
    }
    QSqlQuery q(DatabaseManager::instance().database());
    q.prepare(QStringLiteral("UPDATE users SET username = ? WHERE id_user = ?"));
    q.addBindValue(name.trimmed());
    q.addBindValue(userId);
    if (!q.exec()) qWarning() << q.lastError().text();
    setStatus(QStringLiteral("Nom modifié avec succès."), false);
    loadUsers();
}

void UsersViewModel::changeEmail(int userId, const QString& email) {
    static QRegularExpression re(QStringLiteral(R"(^[^@\s]+@[^@\s]+\.[^@\s]+$)"));
    if (!re.match(email.trimmed()).hasMatch()) {
        setStatus(QStringLiteral("Adresse email invalide."), true);
        return;
    }
    QSqlQuery q(DatabaseManager::instance().database());
    q.prepare(QStringLiteral("UPDATE users SET email = ? WHERE id_user = ?"));
    q.addBindValue(email.trimmed());
    q.addBindValue(userId);
    if (!q.exec()) qWarning() << q.lastError().text();
    setStatus(QStringLiteral("Email modifié avec succès."), false);
    loadUsers();
}

void UsersViewModel::toggleBan(int userId, bool ban) {
    QSqlQuery q(DatabaseManager::instance().database());
    q.prepare(QStringLiteral("UPDATE users SET is_banned = ? WHERE id_user = ?"));
    q.addBindValue(ban);
    q.addBindValue(userId);
    if (!q.exec()) qWarning() << q.lastError().text();
    setStatus(ban ? QStringLiteral("Utilisateur banni.")
                  : QStringLiteral("Utilisateur débanni."), false);
    loadUsers();
}

void UsersViewModel::deleteUser(int userId) {
    QSqlQuery q(DatabaseManager::instance().database());
    q.prepare(QStringLiteral("DELETE FROM users WHERE id_user = ?"));
    q.addBindValue(userId);
    if (!q.exec()) qWarning() << q.lastError().text();
    setStatus(QStringLiteral("Utilisateur supprimé."), false);
    loadUsers();
}

void UsersViewModel::clearStatus() {
    m_statusMsg.clear();
    m_statusError = false;
    emit statusChanged();
}
