#include "DatabaseManager.h"
#include <QSqlQuery>
#include <QSqlError>
#include <QDebug>

DatabaseManager& DatabaseManager::instance() {
    static DatabaseManager inst;
    return inst;
}

bool DatabaseManager::connect(const QString& host, int port,
                               const QString& dbName,
                               const QString& user, const QString& password) {
    const QString connName = QStringLiteral("efoot_conn");
    if (QSqlDatabase::contains(connName))
        QSqlDatabase::removeDatabase(connName);

    m_db = QSqlDatabase::addDatabase(QStringLiteral("QMYSQL"), connName);
    m_db.setHostName(host);
    m_db.setPort(port);
    m_db.setDatabaseName(dbName);
    m_db.setUserName(user);
    m_db.setPassword(password);

    if (!m_db.open()) {
        m_lastError = m_db.lastError().text();
        qWarning() << "[DB] Connection failed:" << m_lastError;
        return false;
    }
    qDebug() << "[DB] Connected to" << dbName << "on" << host;
    return true;
}

bool DatabaseManager::isConnected() const {
    return m_db.isValid() && m_db.isOpen();
}

QSqlDatabase DatabaseManager::database() const {
    return m_db;
}

QString DatabaseManager::lastError() const {
    return m_lastError;
}

QString DatabaseManager::detectRoleColumn() {
    QSqlQuery q(m_db);
    q.exec(
        "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS "
        "WHERE TABLE_SCHEMA = DATABASE() "
        "  AND TABLE_NAME   = 'role' "
        "  AND COLUMN_NAME != 'id_role' "
        "LIMIT 1"
    );
    return q.next() ? q.value(0).toString() : QStringLiteral("nom_role");
}
