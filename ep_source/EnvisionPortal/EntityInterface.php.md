## Table of Contents

- [Introduction](#introduction)
- [Member Properties](#member-properties)
- [Methods](#methods)
- [Example Usage](#example-usage)

## Introduction

The `EntityInterface` interface defines a set of requirements that all entity classes must implement. This interface is intended to provide a consistent and uniform way for interacting with entities in the system.

## Member Properties

The following member properties are defined in the `EntityInterface` interface:

| Property | Type | Description |
|---|---|---|
| `id` | `int` | The unique ID of the entity |

## Methods

The following methods are defined in the `EntityInterface` interface:

| Method | Return Type | Description |
|---|---|---|
| `isAllowed()` | `bool` | Whether the user is allowed to access the entity |
| `getId()` | `int` | The unique ID of the entity |

## Example Usage

The following code shows an example of how to use the `EntityInterface` interface:

```php
<?php

declare(strict_types=1);

use EnvisionPortal\EntityInterface;

class UserEntity implements EntityInterface
{
    private int $id;

    public function isAllowed(): bool
    {
        // Check if the user is allowed to access the entity

        return true;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
```
```php
use EnvisionPortal\EntityInterface;

class TaskEntity implements EntityInterface
{
    private int $id;
    private bool $isAllowed;

    public function __construct(int $id, bool $isAllowed)
    {
        $this->id = $id;
        $this->isAllowed = $isAllowed;
    }

    public function isAllowed(): bool
    {
        return $this->isAllowed;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
```