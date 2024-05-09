## Array Access Trait

### Table of Contents
- Description
- Usage
- Methods

### Description

The `ArrayAccessTrait` provides a uniform interface for accessing and modifying the properties of an object as if it were an array. This allows objects to be used in a more flexible and intuitive manner, particularly when iterating over collections or accessing properties dynamically.

### Usage

To use the `ArrayAccessTrait`, simply include it in the class definition as follows:

```php
use EnvisionPortal\ArrayAccessTrait;

class MyObject implements \ArrayAccess
{
    use ArrayAccessTrait;

    // Properties
}
```

### Methods

The `ArrayAccessTrait` implements the following methods:

| Method | Description |
|---|---|
| offsetExists($offset) | Returns true if the given offset exists, otherwise false. |
| offsetGet($offset) | Fetches the offset if it exists othwerwise return NULL. |
| offsetSet($offset, $value) | Assigns the offset. |
| offsetUnset($offset) | Unsets the offset. |

### Example Usage

The following code shows an example of how to use the `ArrayAccessTrait`:

```php
use EnvisionPortal\ArrayAccessTrait;

class MyObject implements \ArrayAccess
{
    use ArrayAccessTrait;

    // Properties
    private $foo = 'bar';
}

$object = new MyObject();

// Accessing properties as array elements
echo $object['foo']; // Output: bar

// Modifying properties as array elements
$object['foo'] = 'baz';
echo $object['foo']; // Output: baz

// Iterating over properties as an array
foreach ($object as $key => $value) {
    echo "$key => $value";
}
// Output:
// foo => bar
```