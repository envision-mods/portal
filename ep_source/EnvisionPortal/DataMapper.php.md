## DataMapper  📖

### Table of Contents

- **Introduction**
- **Purpose**
- **Implementation**
- **Usage**
- **Additional Notes**

### Introduction

The DataMapper class is responsible for managing the interaction between the PHP application and the database. It provides a set of methods for fetching, inserting, updating, and deleting records from the database.

### Purpose

The purpose of the DataMapper class is to:

- Provide a consistent and efficient way to access the database
- Reduce the amount of boilerplate code required to perform database operations
- Make it easier to maintain the application's database schema

### Implementation

The DataMapper class uses the following techniques to achieve its goals:

- **Database abstraction:** The DataMapper class uses the DatabaseHelper class to abstract away the underlying database implementation. This allows the DataMapper class to be used with any database that is supported by the DatabaseHelper class.
- **Object-oriented programming:** The DataMapper class uses object-oriented programming techniques to represent database records as objects. This makes it easier to work with database records in a consistent and maintainable way.
- **Caching:** The DataMapper class uses caching to improve performance. It caches the results of frequently executed queries so that they can be reused without having to re-execute the query.

### Usage

The DataMapper class can be used to perform the following operations:

- **Fetching records:** The fetchBy() method can be used to fetch records from the database. The method takes a variety of parameters, including the fields to select, the conditions to filter by, and the order to sort the results.
- **Inserting records:** The insert() method can be used to insert a new record into the database. The method takes a single parameter, which is an object representing the record to be inserted.
- **Updating records:** The update() method can be used to update an existing record in the database. The method takes two parameters: an object representing the record to be updated, and an array of fields to update.
- **Deleting records:** The delete() method can be used to delete a record from the database. The method takes a single parameter, which is the ID of the record to be deleted.

### Additional Notes

- The DataMapper class is a powerful tool that can be used to greatly improve the efficiency and maintainability of your PHP applications.
- It is important to use the DataMapper class consistently throughout your application to ensure that your code is consistent and maintainable.
- The DataMapper class is open source and is available on GitHub.

### Example Usage

The following code shows an example of how to use the DataMapper class to fetch a record from the database:

```php
use EnvisionPortal\DataMapper;

$dataMapper = new DataMapper();
$pages = $dataMapper->fetchBy(['*'], [], [], [], [], [], 1, 0);
```

The following code shows an example of how to use the DataMapper class to insert a new record into the database:

```php
use EnvisionPortal\DataMapper;
use EnvisionPortal\Page;

$dataMapper = new DataMapper();
$page = new Page(null, 'test-page', 'Test Page', 'page', 'This is a test page.', ['Guest']);
$dataMapper->insert($page);
```

The following code shows an example of how to use the DataMapper class to update an existing record in the database:

```php
use EnvisionPortal\DataMapper;
use EnvisionPortal\Page;

$dataMapper = new DataMapper();
$page = new Page(1, 'test-page', 'Test Page', 'page', 'This is a test page.', ['Guest']);
$dataMapper->update($page);
```

The following code shows an example of how to use the DataMapper class to delete a record from the database:

```php
use EnvisionPortal\DataMapper;

$dataMapper = new DataMapper();
$dataMapper->delete(new Page(1));
```