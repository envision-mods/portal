## Table of Contents

- [Overview](#overview)
- [Usage](#usage)
- [Options](#options)
- [Example Usage](#example-usage)

## Overview

The `Boardlist` class represents a field that allows users to select one or more boards from a list of available boards. It is typically used in the context of creating or editing a post or topic.

## Usage

The `Boardlist` class can be used in a variety of ways, depending on the specific needs of the application. However, the following general steps are typically involved:

1. Create a new `Boardlist` object.
2. Set the `value` property of the object to an array of the IDs of the boards that should be selected by default.
3. Render the `Boardlist` object using the `__toString()` method.

## Options

The `Boardlist` class supports the following options:

| Option | Default | Description |
|---|---|---|
| `value` | `[]` | An array of the IDs of the boards that should be selected by default. |

## Example Usage

The following code shows how to create a `Boardlist` object and render it:

```php
$field = [
    'name' => 'boards',
    'title' => 'Boards',
    'type' => 'boardlist',
    'value' => [],
];

$boardlist = new Boardlist($field, 'boards', 'boardlist');

echo $boardlist->__toString();
```

The above code will render a `<fieldset>` element with a legend that says "Boards". Inside the fieldset, there will be a `<ul>` element with a `<li>` element for each board. Each `<li>` element will contain a `<label>` element with a checkbox and the name of the board. The checkboxes will be checked if the corresponding board is included in the `value` array of the `Boardlist` object.