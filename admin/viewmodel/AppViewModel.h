#pragma once
#include "BaseViewModel.h"
#include "User.h"
#include <QString>

class AppViewModel : public BaseViewModel {
    Q_OBJECT

    Q_PROPERTY(bool    loggedIn       READ loggedIn       NOTIFY authChanged)
    Q_PROPERTY(bool    isAdmin        READ isAdmin        NOTIFY authChanged)
    Q_PROPERTY(QString username       READ username       NOTIFY authChanged)
    Q_PROPERTY(int     currentPage    READ currentPage    WRITE setCurrentPage  NOTIFY currentPageChanged)
    Q_PROPERTY(int     currentCupId   READ currentCupId   NOTIFY currentCupChanged)
    Q_PROPERTY(QString currentCupName READ currentCupName NOTIFY currentCupChanged)

public:
    explicit AppViewModel(QObject* parent = nullptr);

    bool    loggedIn()       const;
    bool    isAdmin()        const;
    QString username()       const;
    int     currentPage()    const;
    int     currentCupId()   const;
    QString currentCupName() const;

    void setCurrentPage(int page);

    Q_INVOKABLE void selectCup(int id, const QString& name);
    Q_INVOKABLE void logout();

public slots:
    void onLoginSuccess(const User& user);

signals:
    void authChanged();
    void currentPageChanged();
    void currentCupChanged();

private:
    User    m_user;
    bool    m_loggedIn      = false;
    int     m_currentPage   = 0;
    int     m_currentCupId  = -1;
    QString m_currentCupName;
};
