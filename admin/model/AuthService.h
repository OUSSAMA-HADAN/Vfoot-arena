#pragma once
#include <QObject>
#include <QString>
#include "User.h"

class AuthService : public QObject {
    Q_OBJECT
public:
    explicit AuthService(QObject* parent = nullptr);

    bool connectDatabase(const QString& host, int port,
                         const QString& dbName,
                         const QString& user,
                         const QString& password);



    bool    authenticate(const QString& email,
                         const QString& password,
                         User&          outUser);

    QString lastError() const;

private:
    QString m_lastError;
};
