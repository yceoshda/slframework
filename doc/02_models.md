# Models

Let's talk about the basic models support.

All models should inherit from the Model class.

The model class uses iConnect_PDO to handle database connections. As the name suggests, the back end is PDO!

## Properties

Some properties can (and should) be overloaded when creating your model.

$table is used by the basic CRUD generator to create proper queries.

$primaryKey is set to 'id' by default. Feel free to overload it if your table uses something else (like 'uid' for example).

$validation should contain the validation rules that are to be used by the data validator (See Data validation topic underneath).

## Injection

To inject the model in the current controller, you can use the integrated injector. This will load the model object (if not already loaded) in a controller property named after the model name. This will also pass it the database connection and the configuration.

```php
$controller->loadModel('ModelName');

//  or for specific Namespaces needs

$controller->loadModel('ModelName', 'Model\Name\Space');
```

You can autoload models when instanciating controller (View controller doc).

## Basic CRUD

The model class offers some (very) basic CRUD query generator. So it includes these 3 methods:

- find, used to retrieve things from database.
- delete, used to delete something from the database.
- save, to insert or update data.

### Find

Find can be used to send basic select queries. There is also a findFirst method that basically returns only the first result in the dataset.

If no rows are returned find will return false (boolean).

Parameters are to be sent as an array.

By default, the select query will be performed on the table represented by the class.

## Data Validation

Because you should never trust data that you haven't generated yourself (user input for instance), you should always validate the data before sending it to the database. This is also true even if you are using PDO (as this is) that is sanitizing things. Even if the router itself can clean up a few things. **Better be safe than sorry**.

### The rules

The rules should be added to the $validation property as array(s). The key being the name of the property in the data (i.e.: $data->name will have a 'name' => [ ... rules ...]). They will then be automatically formatted into $this->validations_rules.

There are 4 types of rules that can be applied to any single value. Any failure to "comply" will set the validation to false and set the error in the rule set.

#### Required

You can use the 'required' type to specify length required, or mandatory presence. This can be either a single number (from 0 to N) to specify a minimum but no max, or an array of 2 numbers for min and max length.

```php

//  min 2 and no max
$rule = array('required' => 2);

//  min 8 characters, max 32
$rule = array('required' => [8, 32]);

//  must be empty
$rule = array('required' => [0, 0]);

```

#### Filter

The 'filter' type allows you to specify allowed characters using a basic regular expression. Although it can be "reversed" (as the ^ part is actually user defined), it's main goal is to filter unwanted characters.

```php

//  only numbers
$rule = array('filter' => '^0-9');

//  only caps and numbers
$rule = array('filter' => '^A-Z0-9');

```

Additionnally this rule can be kind of bypassed by using a second rule type: 'filter_clean' (that obviously has no meaning without a filter first). This will actually remove the unwanted characters from the input and prevent validation from failing. This can be usefull to forcefully sanitize something no so important!

#### Pattern

You can specify a pattern for the data value (again using a regex).

```php

//  a number and 4 letters
$rule = array('pattern' => '^[0-9][a-zA-Z]{4}$');

//  an email
$rule = array('pattern' => '^[0-9a-z_.-]+@[0-9a-z_.-]+$');

```

#### Extra

The 'extra' type is to be user defined. If used, it should be the name of the method from your model that will be used to validate the data (and it must return true or false).

This method will be called with the data as parameter (usefull if you need to compare 2 values for example).

### Using it

To validate data, data must be an object values being properties.

Simply call the validate method passing it the rules to apply and the data to be assessed. (And no, since you can have non-existent values in the data, rules cannot be auto-detected :-( ).

The validate method will return (boolean) false if something fails, and validation_errors will contain info about the error (validation_errors is an array, data names as keys, rule failed as values).

```php

//  applying a few rules
$rules_to_apply = ['id', 'name', 'email'];
$validated = $this->validate($rules_to_apply, $data);

```
