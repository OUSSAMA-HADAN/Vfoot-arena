#pragma once
#include <QSqlDatabase>

class DatabaseManager {
public:
    static DatabaseManager& instance();

    bool         connect(const QString& host, int port,
                         const QString& dbName,
                         const QString& user, const QString& password);
    bool         isConnected()  const;
    QSqlDatabase database()     const;
    QString      lastError()    const;

    QString detectRoleColumn();

private:
    DatabaseManager() = default;

    QSqlDatabase m_db;
    QString      m_lastError;
};
