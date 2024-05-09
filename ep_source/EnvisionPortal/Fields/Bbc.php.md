## PHP: Envision Portal 3.0.0: Bbc Field Class 💻

### Table of Contents 📖

- [Description](#description)
- [Constructor](#constructor)
- [Instance Methods](#instance-methods)
    - [`__toString()`](#__tostring)

### Description 📋

The `Bbc` class provides a BBC Code input field type for Envision Portal 3.0.0. It uses the SMF built-in WYSIWYG editor for text input.

### Constructor 👷

```php
public function __construct(array $field, string $key, string $type)
```

- `$field`: The field configuration from the database.
- `$key`: The unique key of the field.
- `$type`: The type of field.

### Instance Methods 🛠️

#### `__toString()` 📖

Returns the HTML code for the field.

```php
public function __toString(): string
```

#### Example Usage 💡

```php
$field = [
    'value' => '',
];

$bbcField = new Bbc($field, 'my_bbc_field', 'bbc');

echo $bbcField;
```