### Table of Contents

1. Overview
2. Features
3. Usage
4. Example

---

### 1️⃣ Overview

The `Checklist` class represents a checklist field in the Envision Portal content management system. It allows users to select multiple options from a predefined list.

---

### 2️⃣ Features

- Supports both static and ordered options
- Provides a user-friendly interface for selecting options
- Validates input before saving to the database

---

### 3️⃣ Usage

To use the `Checklist` class, you must first create a field definition array. This array should include the following keys:

- `name`: The name of the field
- `type`: The type of field (in this case, "checklist")
- `options`: An array of options for the checklist
- `value`: The default value for the checklist

Once you have created the field definition array, you can create a new `Checklist` object by passing the array to the constructor. The constructor will automatically generate a list of options and their corresponding checkboxes.

To save the selected options, you can use the `beforeSave()` method. This method will return a string that contains the selected options in a format that can be stored in the database.

---

### 4️⃣ Example

The following code shows an example of how to use the `Checklist` class:

```php
<?php

// Create a field definition array
$field = [
    'name' => 'interests',
    'type' => 'checklist',
    'options' => [
        'sports',
        'music',
        'movies',
    ],
    'value' => '',
];

// Create a new Checklist object
$checklist = new Checklist($field, 'interests', 'user');

// Display the checklist
echo $checklist;

// Save the selected options
$selected = $checklist->beforeSave($_POST['interests']);

// Store the selected options in the database
$query = "UPDATE users SET interests = '$selected' WHERE id = 1";
$result = $db->query($query);

?>
```

This code will create a checklist with three options: "sports", "music", and "movies". The user can select multiple options from the list. When the form is submitted, the `beforeSave()` method will be called to validate the input and return a string that contains the selected options. This string can then be stored in the database.