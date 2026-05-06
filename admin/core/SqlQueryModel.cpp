#include "SqlQueryModel.h"
#include <QSqlQuery>
#include <QSqlRecord>
#include <QSqlError>
#include <QDebug>

SqlQueryModel::SqlQueryModel(QObject* parent)
    : QSqlQueryModel(parent) {}

void SqlQueryModel::queryChange() {
    QSqlQueryModel::queryChange();
    emit headersChanged();
}

int SqlQueryModel::rowCount() const {
    return QSqlQueryModel::rowCount();
}

int SqlQueryModel::colCount() const {
    return record().count();
}

QStringList SqlQueryModel::headers() const {
    QStringList h;
    const QSqlRecord rec = record();
    for (int i = 0; i < rec.count(); ++i)
        h << rec.fieldName(i);
    return h;
}

QVariantList SqlQueryModel::rowData(int row) const {
    QVariantList data;
    const QSqlRecord rec = record(row);
    for (int i = 0; i < rec.count(); ++i)
        data << rec.value(i);
    return data;
}

void SqlQueryModel::refresh(const QString& sql, const QSqlDatabase& db) {
    QSqlQuery q(db);
    if (!q.exec(sql))
        qWarning() << "[SqlQueryModel] refresh error:" << q.lastError().text();
    setQuery(std::move(q));
}
