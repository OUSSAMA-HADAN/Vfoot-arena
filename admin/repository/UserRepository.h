#pragma once
#include "SqlQueryModel.h"
#include <QString>

class UserRepository {
public:
    void    loadAll(SqlQueryModel* model);
    bool    isBanned(int userId) const;
    bool    changeName(int userId, const QString& name);
    bool    changeEmail(int userId, const QString& email);
    bool    toggleBan(int userId, bool ban);
    bool    deleteUser(int userId);
    QString lastError() const;

private:
    QString m_lastError;
    bool    execWrite(const QString& sql, const QVariantList& bindings);
};
