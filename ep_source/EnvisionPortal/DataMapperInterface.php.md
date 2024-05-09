## Table of Contents

1. [Introduction](#introduction)
2. [Methods](#methods)
    - [fetchBy](#fetchby)
    - [insert](#insert)
    - [update](#update)
    - [delete](#delete)
    - [deleteMany](#deletemany)
    - [deleteAll](#deleteall)

## Introduction

The `DataMapperInterface` defines the interface for data mappers that are responsible for mapping entities to and from a database.

## Methods

### fetchBy

The `fetchBy` method generates a query about attachment data and yields the result rows.

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `selects` | array | Table columns to select. |
| `params` | array | Parameters to substitute into query text. |
| `joins` | array | One or more *complete* JOIN clauses.<br>E.g.: 'LEFT JOIN {db_prefix}messages AS m ON (a.id_msg = m.id_msg)' |
| `where` | array | Zero or more conditions for the WHERE clause.<br>Conditions will be placed in parentheses and concatenated with AND.<br>If this is left empty, no WHERE clause will be used. |
| `order` | array | Zero or more conditions for the ORDER BY clause.<br>If this is left empty, no ORDER BY clause will be used. |
| `group` | array | Zero or more conditions for the GROUP BY clause. |
| `limit` | int | Maximum number of results to retrieve.<br>If this is left empty, all results will be retrieved. |
| `offset` | int | Offset to start retrieving results from. |

**Returns:**

An array of the result as associative array of database rows.

### insert

The `insert` method inserts an entity into the database.

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `entity` | EntityInterface | The entity to insert. |

### update

The `update` method updates an entity in the database.

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `entity` | EntityInterface | The entity to update. |

### delete

The `delete` method deletes an entity from the database.

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `entity` | EntityInterface | The entity to delete. |

### deleteMany

The `deleteMany` method deletes multiple entities from the database.

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `ids` | array | The IDs of the entities to delete. |

### deleteAll

The `deleteAll` method deletes all entities of a given type from the database.

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `entity` | EntityInterface | The type of entity to delete. |

## Example Usage

The following code shows an example of how to use the `DataMapperInterface` to fetch data from the database:

```php
use EnvisionPortal\DataMapperInterface;

class AttachmentDataMapper implements DataMapperInterface
{
    // ...

    public function fetchBy(
        array $selects,
        array $params = [],
        array $joins = [],
        array $where = [],
        array $order = [],
        array $group = [],
        int $limit = null,
        int $offset = null
    ): array
    {
        // ...

        return $rows;
    }

    // ...
}

// ...

$dataMapper = new AttachmentDataMapper();
$rows = $dataMapper->fetchBy(['id_attach', 'filename'], ['id_msg' => 1]);
```