#pragma once
#include "BaseViewModel.h"
#include "User.h"
#include <QString>

class AuthService;

class LoginViewModel : public BaseViewModel {
    Q_OBJECT

    Q_PROPERTY(QString statusMessage READ statusMessage NOTIFY statusChanged)
    Q_PROPERTY(bool    isError       READ isError       NOTIFY statusChanged)
    Q_PROPERTY(bool    isBusy        READ isBusy        NOTIFY busyChanged)

public:
    explicit LoginViewModel(AuthService* authService, QObject* parent = nullptr);

    QString statusMessage() const;
    bool    isError()       const;
    bool    isBusy()        const;

    Q_INVOKABLE void login(const QString& email, const QString& password);
    Q_INVOKABLE void clearStatus();


    static bool isValidEmail(const QString& email);
    static bool isValidPassword(const QString& password);

signals:
    void statusChanged();
    void busyChanged();
    void loginSuccess(const User& user);
    void loginFailed();

private:
    AuthService* m_auth;
    QString      m_statusMessage;
    bool         m_isError = false;
    bool         m_isBusy  = false;

    void setStatus(const QString& msg, bool isError);
    void setBusy(bool busy);
};
