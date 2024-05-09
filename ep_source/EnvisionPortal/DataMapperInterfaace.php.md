## 📚 DataMapper Interface

### 🔗 Table of Contents

- [Introduction](#introduction)
- [Methods](#methods)
    - [getIdInfo](#getidinfo)
    - [getColumnsToInsert](#getcolumnstoinsert)
    - [getColumnsToUpdate](#getcolumnstoupdate)
    - [getTableName](#gettablename)
    - [fetchBy](#fetchby)
    - [insert](#insert)
    - [update](#update)
    - [delete](#delete)
    - [deleteMany](#deletemany)
    - [deleteAll](#deleteall)
    - [getColumnInfo](#getcolumninfo)

### 📝 Introduction

The `DataMapperInterface` is a contract that defines a set of methods for interacting with a database.
It provides a common interface for accessing and manipulating data in a database-agnostic manner.
This interface is used by the `EntityMapper` class to perform CRUD operations on entities.

### 🧰 Methods

#### getIdInfo

```php
public function getIdInfo(): string;
```

Returns the name of the primary key column for the table associated with the mapper.

#### getColumnsToInsert

```php
public function getColumnsToInsert(): array;
```

Returns an array of column names that should be included in an INSERT statement for the table associated with the mapper.

#### getColumnsToUpdate

```php
public function getColumnsToUpdate(): array;
```

Returns an array of column names that should be included in an UPDATE statement for the table associated with the mapper.

#### getTableName

```php
public function getTableName(): string;
```

Returns the name of the table associated with the mapper.

#### fetchBy

```php
public function fetchBy(
    array $selects,
    array $params = [],
    array $joins = [],
    array $where = [],
    array $order = [],
    array $group = [],
    int $limit = null,
    int $offset = null
): array;
```

Fetches rows from the database based on the given query parameters.

**Parameters:**

* `selects`: An array of column names to select.
* `params`: An array of parameters to substitute into the query text.
* `joins`: An array of JOIN clauses.
* `where`: An array of conditions for the WHERE clause.
* `order`: An array of conditions for the ORDER BY clause.
* `group`: An array of conditions for the GROUP BY Clause.
* `limit`: The maximum number of results to retrieve.
* `offset`: The offset of the first result to retrieve.

**Returns:**

An array of associative arrays representing the database rows.

#### insert

```php
public function insert(EntityInterface $entity): void;
```

Inserts a new row into the database for the given entity.

**Parameters:**

* `entity`: The entity to insert.

#### update

```php
public function update(EntityInterface $entity): void;
```

Updates an existing row in the database for the given entity.

**Parameters:**

* `entity`: The entity to update.

#### delete

```php
public function delete(EntityInterface $entity): void;
```

Deletes a row from the database for the given entity.

**Parameters:**

* `entity`: The entity to delete.

#### deleteMany

```php
public function deleteMany(array $ids): void;
```

Deletes multiple rows from the database based on the given array of IDs.

**Parameters:**

* `ids`: An array of IDs.

#### deleteAll

```php
public function deleteAll(EntityInterface $entity): void;
```

Deletes all rows from the database for the given entity.

**Parameters:**

* `entity`: The entity to delete all rows for.

#### getColumnInfo

```php
public function getColumnInfo(): array;
```

Returns an array of column information for the table associated with the mapper.

**Returns:**

An array of associative arrays representing the column information.