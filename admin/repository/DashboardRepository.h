#pragma once
#include "SqlQueryModel.h"

class DashboardRepository {
public:
    void loadCups(SqlQueryModel* model);
    int  countUsers()    const;
    int  countCups()     const;
    int  countMatches()  const;
    int  countTrophies() const;

private:
    int queryCount(const QString& sql) const;
};
