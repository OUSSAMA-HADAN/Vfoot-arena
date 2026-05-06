#pragma once
#include <QString>

struct User {
    int     idUser  ;
    QString username;
    QString email;
    QString role;
    bool    isBanned = false;
};
