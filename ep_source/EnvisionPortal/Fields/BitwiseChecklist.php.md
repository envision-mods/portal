## Table of Contents

- [Overview](#overview)
- [Before Save Method](#before-save-method)
- [ToString Method](#tostring-method)
- [Example Usage](#example-usage)

## Overview

The `BitwiseChecklist` class implements the `UpdateFieldInterface` and provides a bitwise checklist field type for Envision Portal, a content management system.

By implementing the `UpdateFieldInterface`, it provides the `beforeSave` method for sanitizing and validating data before saving to the database. Additionally, it provides a `__toString` method for rendering the field in the admin panel.

## Before Save Method

### Syntax

```php
public function beforeSave($val): string
```

### Parameters

- `$val`: The value of the field

### Return Value

- `string`: The sanitized and validated value

### Description

The `beforeSave` method sanitizes and validates the input data for the field. It converts the input array into a bitwise integer, where each option in the field corresponds to a bit in the integer. If the option is selected, the corresponding bit is set to 1. Otherwise, it is set to 0.

## ToString Method

### Syntax

```php
public function __toString(): string
```

### Return Value

- `string`: The HTML representation of the field

### Description

The `__toString` method renders the field in the admin panel as a checkbox list. Each checkbox corresponds to an option in the field, and the checkbox is checked if the corresponding bit in the field's value is set.

## Example Usage

The following code shows an example of how to use the `BitwiseChecklist` class:

```php
$field = [
    'key' => 'my_bitwise_checklist',
    'type' => 'bitwise_checklist',
    'options' => [
        1 => 'Option 1',
        2 => 'Option 2',
        4 => 'Option 3',
    ],
    'value' => 3, // Option 1 and Option 2 are selected
];

$bitwiseChecklist = new BitwiseChecklist($field, 'my_bitwise_checklist', 'bitwise_checklist');

$html = $bitwiseChecklist->beforeSave(['Option 2']); // Select Option 2

echo $html; // Renders the checklist in the admin panel
```