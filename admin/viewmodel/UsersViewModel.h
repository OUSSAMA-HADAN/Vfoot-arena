#pragma once
#include "BaseViewModel.h"
#include "SqlQueryModel.h"
#include "UserRepository.h"
#include <QString>

class UsersViewModel : public BaseViewModel {
    Q_OBJECT

    Q_PROPERTY(SqlQueryModel* usersModel READ usersModel CONSTANT)
    Q_PROPERTY(QString statusMsg         READ statusMsg   NOTIFY statusChanged)
    Q_PROPERTY(bool    statusError       READ statusError NOTIFY statusChanged)

public:
    explicit UsersViewModel(QObject* parent = nullptr);

    SqlQueryModel* usersModel()  const;
    QString        statusMsg()   const;
    bool           statusError() const;

    Q_INVOKABLE void    loadUsers();
    Q_INVOKABLE int     userIdAtRow(int row)     const;
    Q_INVOKABLE QString userNameAtRow(int row)   const;
    Q_INVOKABLE QString userEmailAtRow(int row)  const;
    Q_INVOKABLE bool    userIsBannedById(int id) const;
    Q_INVOKABLE void    changeName(int userId, const QString& name);
    Q_INVOKABLE void    changeEmail(int userId, const QString& email);
    Q_INVOKABLE void    toggleBan(int userId, bool ban);
    Q_INVOKABLE void    deleteUser(int userId);
    Q_INVOKABLE void    clearStatus();

signals:
    void statusChanged();

private:
    UserRepository m_repo;
    SqlQueryModel* m_usersModel;
    QString        m_statusMsg;
    bool           m_statusError = false;

    void setStatus(const QString& msg, bool error);
};
