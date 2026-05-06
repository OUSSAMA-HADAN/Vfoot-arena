#include "LoginViewModel.h"
#include "AuthService.h"
#include <QRegularExpression>

LoginViewModel::LoginViewModel(AuthService* authService, QObject* parent)
    : BaseViewModel(parent), m_auth(authService) {}

QString LoginViewModel::statusMessage() const { return m_statusMessage; }
bool    LoginViewModel::isError()       const { return m_isError; }
bool    LoginViewModel::isBusy()        const { return m_isBusy; }

void LoginViewModel::setStatus(const QString& msg, bool isError) {
    m_statusMessage = msg;
    m_isError       = isError;
    emit statusChanged();
}

void LoginViewModel::setBusy(bool busy) {
    m_isBusy = busy;
    emit busyChanged();
}

bool LoginViewModel::isValidEmail(const QString& email) {
    static QRegularExpression re(QStringLiteral(R"(^[^@\s]+@[^@\s]+\.[^@\s]+$)"));
    return re.match(email.trimmed()).hasMatch();
}

bool LoginViewModel::isValidPassword(const QString& password) {
    return password.length() >= 4;
}

void LoginViewModel::login(const QString& email, const QString& password) {

    if (email.trimmed().isEmpty() || password.isEmpty()) {
        setStatus(QStringLiteral("Veuillez remplir tous les champs."), true);
        emit loginFailed();
        return;
    }
    if (!isValidEmail(email)) {
        setStatus(QStringLiteral("Adresse email invalide."), true);
        emit loginFailed();
        return;
    }
    if (!isValidPassword(password)) {
        setStatus(QStringLiteral("Mot de passe trop court (min. 4 caractères)."), true);
        emit loginFailed();
        return;
    }

    setBusy(true);
    setStatus(QString(), false);


    User user;
    const bool ok = m_auth->authenticate(email.trimmed(), password, user);

    setBusy(false);

    if (ok) {
        setStatus(QStringLiteral("Connexion réussie !"), false);
        emit loginSuccess(user);
    } else {
        setStatus(m_auth->lastError(), true);
        emit loginFailed();
    }
}

void LoginViewModel::clearStatus() {
    m_statusMessage.clear();
    m_isError = false;
    emit statusChanged();
}
