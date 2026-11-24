#include "UsersViewModel.h"
#include "UserRepository.h"
#include <QRegularExpression>

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
    m_repo.loadAll(m_usersModel);
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
    return m_repo.isBanned(id);
}

void UsersViewModel::changeName(int userId, const QString& name) {
    if (name.trimmed().isEmpty()) {
        setStatus(QStringLiteral("Le nom ne peut pas être vide."), true);
        return;
    }
    if (m_repo.changeName(userId, name.trimmed()))
        setStatus(QStringLiteral("Nom modifié avec succès."), false);
    else
        setStatus(m_repo.lastError(), true);
    loadUsers();
}

void UsersViewModel::changeEmail(int userId, const QString& email) {
    static QRegularExpression re(QStringLiteral(R"(^[^@\s]+@[^@\s]+\.[^@\s]+$)"));
    if (!re.match(email.trimmed()).hasMatch()) {
        setStatus(QStringLiteral("Adresse email invalide."), true);
        return;
    }
    if (m_repo.changeEmail(userId, email.trimmed()))
        setStatus(QStringLiteral("Email modifié avec succès."), false);
    else
        setStatus(m_repo.lastError(), true);
    loadUsers();
}

void UsersViewModel::toggleBan(int userId, bool ban) {
    if (m_repo.toggleBan(userId, ban))
        setStatus(ban ? QStringLiteral("Utilisateur banni.")
                      : QStringLiteral("Utilisateur débanni."), false);
    else
        setStatus(m_repo.lastError(), true);
    loadUsers();
}

void UsersViewModel::deleteUser(int userId) {
    if (m_repo.deleteUser(userId))
        setStatus(QStringLiteral("Utilisateur supprimé."), false);
    else
        setStatus(m_repo.lastError(), true);
    loadUsers();
}

void UsersViewModel::clearStatus() {
    m_statusMsg.clear();
    m_statusError = false;
    emit statusChanged();
}
