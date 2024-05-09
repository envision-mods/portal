## Table of Contents

- [Database Helper Class](#database-helper-class)
    - [Generator](#generator)
    - [Insert](#insert)
    - [Update](#update)
    - [Delete](#delete)
    - [Delete Many](#delete-many)
    - [Delete All](#delete-all)
    - [Increment](#increment)

## Database Helper Class

The `DatabaseHelper` class provides a set of utility methods for working with the database. These methods can be used to simplify common database operations, such as fetching data, inserting new rows, updating existing rows, and deleting rows.

### Generator

The `fetchBy` method is a generator that runs queries about attachment data and yields the result rows. It can be used as follows:

```php
foreach ($databaseHelper->fetchBy(['id_attach', 'filename'], 'attachments', [], [], [], [], [], 10) as $row) {
    // Do something with the row
}
```

The `fetchBy` method takes the following parameters:

- `selects`: An array of table columns to select.
- `from`: The FROM clause.
- `params`: An array of parameters to substitute into the query text.
- `joins`: An array of zero or more complete JOIN clauses.
- `where`: An array of zero or more conditions for the WHERE clause.
- `order`: An array of zero or more conditions for the ORDER BY clause.
- `limit`: The maximum number of results to retrieve.
- `offset`: The offset of the first result to retrieve.

### Insert

The `insert` method inserts a new row into a table. It takes the following parameters:

- `table_name`: The name of the table to insert into.
- `columns`: An associative array of column names and values.

```php
$databaseHelper->insert('attachments', [
    'id_attach' => ['int', 1],
    'filename' => ['string', 'test.txt'],
    'size' => ['int', 1024],
]);
```

### Update

The `update` method updates an existing row in a table. It takes the following parameters:

- `table_name`: The name of the table to update.
- `columns`: An associative array of column names and values.
- `col`: The column name of the row to update.
- `id`: The value of the column to update.

```php
$databaseHelper->update('attachments', [
    'filename' => ['string', 'new_test.txt'],
], 'id_attach', 1);
```

### Delete

The `delete` method deletes a row from a table. It takes the following parameters:

- `table_name`: The name of the table to delete from.
- `col`: the column name of the row to delete.
- `id`: The value of the column to delete.

```php
$databaseHelper->delete('attachments', 'id_attach', 1);
```

### Delete Many

The `deleteMany` method deletes multiple rows from a table. It takes the following parameters:

- `table_name`: The name of the table to delete from.
- `col`: the column name of the row to delete.
- `ids`: An array of the values of the column to delete.

```php
$databaseHelper->deleteMany('attachments', 'id_attach', [1, 2, 3]);
```

### Delete All

The `deleteAll` method deletes all rows from a table. It takes the following parameter:

- `table_name`: The name of the table to delete from.

```php
$databaseHelper->deleteAll('attachments');
```

### Increment

The `increment` method increments the value of a column in a table. It takes the following parameters:

- `table_name`: The name of the table to update.
- `increment_col`: The column name to increment.
- `where_col`: The column name of the row to increment.
- `id`: The value of the column to increment.

```php
$databaseHelper->increment('attachments', 'size', 'id_attach', 1);
```