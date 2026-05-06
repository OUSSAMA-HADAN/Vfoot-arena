#pragma once
#include <QObject>
#include <QSqlDatabase>
#include <QSqlQuery>
#include <QVariantList>

class DatabaseManager : public QObject {
    Q_OBJECT
public:
    static DatabaseManager& instance();

    bool    connect(const QString& host, int port,
                    const QString& dbName,
                    const QString& user, const QString& password);
    bool    isConnected() const;
    QSqlQuery exec(const QString& sql);
    QSqlDatabase database() const;
    QString lastError()  const;



    static QString buildSql(const QString& templ, const QVariantList& params);

    QString detectRoleColumn();

private:
    explicit DatabaseManager(QObject* parent = nullptr);
    ~DatabaseManager() override = default;

    QSqlDatabase m_db;
    QString      m_lastError;
};
