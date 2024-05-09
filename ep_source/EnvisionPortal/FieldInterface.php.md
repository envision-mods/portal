## Table of Contents

- Introduction
- FieldInterface
  - __construct()
  - __toString()
- CacheableFieldInterface
  - fetchData()
  - setData()
- UpdateFieldInterface
  - beforeSave()
- Example Usage

## Introduction

This document provides internal code documentation for the `FieldInterface`, `CacheableFieldInterface`, and `UpdateFieldInterface` interfaces used in the Envision Portal project. These interfaces define the functionality that all field classes must implement.

## FieldInterface

The `FieldInterface` interface defines the basic functionality that all field classes must implement.

### __construct()

The `__construct()` method is the constructor for the `FieldInterface` interface. It takes three parameters:

* `$field`: An array of field data.
* `$value`: The value of the field.
* `$type`: The type of the field.

### __toString()

The `__toString()` method returns the HTML control for the field.

## CacheableFieldInterface

The `CacheableFieldInterface` interface extends the `FieldInterface` interface and defines additional functionality for fields that can be cached.

### fetchData()

The `fetchData()` method fetches data from the database to "cache", or store in memory. This is useful when multiple fields of the same type are loaded and a static (unchanging) query is used to fetch data.

### setData()

The `setData()` method grabs shared data from another field of the same type.

## UpdateFieldInterface

The `UpdateFieldInterface` interface extends the `FieldInterface` interface and defines additional functionality for fields that can be updated.

### beforeSave()

The `beforeSave()` method transforms data right before it is saved. This is useful for performing any necessary data validation or transformation before the data is saved to the database.

## Example Usage

The following code shows an example of how to use the `FieldInterface`, `CacheableFieldInterface`, and `UpdateFieldInterface` interfaces:

```php
use EnvisionPortal\FieldInterface;
use EnvisionPortal\CacheableFieldInterface;
use EnvisionPortal\UpdateFieldInterface;

class MyField implements FieldInterface, CacheableFieldInterface, UpdateFieldInterface
{
    public function __construct(array $field, string $value, string $type)
    {
        // ...
    }

    public function __toString(): string
    {
        // ...
    }

    public function fetchData(): array
    {
        // ...
    }

    public function setData(array $data): void
    {
        // ...
    }

    public function beforeSave(?string $val): string
    {
        // ...
    }
}
```

The following code shows an example of how to use the `MyField` class:

```php
$field = new MyField($fieldData, $value, $type);
echo $field; // Output the HTML control for the field.
$data = $field->fetchData(); // Fetch data from the database to cache.
$field->setData($data); // Grab shared data from another field of the same type.
$val = $field->beforeSave($val); // Transform data right before it is saved.
```