#include "DatabaseManager.h"
#include <QSqlError>
#include <QDebug>

DatabaseManager& DatabaseManager::instance() {
    static DatabaseManager inst;
    return inst;
}

DatabaseManager::DatabaseManager(QObject* parent) : QObject(parent) {}

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

QSqlQuery DatabaseManager::exec(const QString& sql) {
    QSqlQuery q(m_db);
    if (!q.exec(sql)) {
        qWarning() << "[SQL] Error:" << q.lastError().text();
        qWarning() << "[SQL] Query:" << sql;
    }
    return q;
}

QSqlDatabase DatabaseManager::database() const {
    return m_db;
}

QString DatabaseManager::lastError() const {
    return m_lastError;
}

QString DatabaseManager::detectRoleColumn() {
    QSqlQuery q = exec(
        "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS "
        "WHERE TABLE_SCHEMA = DATABASE() "
        "  AND TABLE_NAME   = 'role' "
        "  AND COLUMN_NAME != 'id_role' "
        "LIMIT 1"
    );
    return q.next() ? q.value(0).toString() : QStringLiteral("nom_role");
}

QString DatabaseManager::buildSql(const QString& templ, const QVariantList& params) {
    QString sql = templ;
    int searchFrom = 0;
    for (const QVariant& p : params) {
        int idx = sql.indexOf('?', searchFrom);
        if (idx < 0) break;

        QString val;
        switch (p.typeId()) {
            case QMetaType::Int:
            case QMetaType::LongLong:
            case QMetaType::UInt:
            case QMetaType::ULongLong:
                val = p.toString();
                break;
            case QMetaType::Bool:
                val = p.toBool() ? QStringLiteral("1") : QStringLiteral("0");
                break;
            default: {
                QString s = p.toString();
                s.replace(QStringLiteral("'"), QStringLiteral("''"));
                val = QStringLiteral("'") + s + QStringLiteral("'");
                break;
            }
        }
        sql.replace(idx, 1, val);
        searchFrom = idx + val.length();
    }
    return sql;
}
