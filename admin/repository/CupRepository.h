#pragma once
#include "SqlQueryModel.h"
#include <QString>

class CupRepository {
public:
    void    loadAll(SqlQueryModel* model);
    int     idAtRow(SqlQueryModel* model, int row) const;
    QString titleAtRow(SqlQueryModel* model, int row) const;
};
