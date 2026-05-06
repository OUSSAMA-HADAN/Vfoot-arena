#pragma once
#include <QSqlQueryModel>
#include <QSqlDatabase>
#include <QVariantList>
#include <QStringList>

class SqlQueryModel : public QSqlQueryModel {
    Q_OBJECT


    Q_PROPERTY(QStringList headers READ headers NOTIFY headersChanged)
    Q_PROPERTY(int rowCount READ rowCount NOTIFY headersChanged)

public:
    explicit SqlQueryModel(QObject* parent = nullptr);

    Q_INVOKABLE int          rowCount()          const;
    Q_INVOKABLE int          colCount()          const;
    Q_INVOKABLE QStringList  headers()           const;
    Q_INVOKABLE QVariantList rowData(int row)    const;

    void refresh(const QString& sql, const QSqlDatabase& db);

signals:
    void headersChanged();

protected:

    void queryChange() override;
};
