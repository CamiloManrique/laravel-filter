# Laravel Eloquent Model Filter

This package allows querying your Eloquent models, based on URL queries. Using some simple rules, you can even filter based on related models and use diferent SQL comparison operators.

## Instalation

Require package with composer:

```
composer require camilo-manrique/laravel-filter
```

Add CamiloManrique\ResourceFilter to your service providers on your config/app.php:

```php

'providers' => [
    CamiloManrique\ResourceFilter\FilterServiceProvider::class
]

```

You can publish the configuration file to change the default settings:

```
php artisan vendor:publish
```


Depending on your database column names and personal preferences, you might need change some of the defaults.

## Usage

To filter your models, just add the Filterable trait to the models you wish to filter (don't forget the CamiloManrique\ResourceFilter\Filterable namespace):

```php
<?php

namespace App;

use CamiloManrique\ResourceFilter\Filterable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Model
{
    use Filterable;

    /* The rest of your model goes here */
    

}
```

After that you can filter your models in two ways. Both of them receive the Http Request as argument:
Get a query builder instance:

```php
User::filter($request)
```
 
 With this method you get a query builder, in which you can keep applying query builder methods, including `get()`, `first()`, `paginate()` and many others.
  
Get a model instance:

```php
User::filterandGet($request)
```

This method handles the query building and fetching for you. It even handles pagination for you out of the box. For default, automatic pagination using this method is turned
on, but you can change this behavior publishing the configuration file and editing it.

##### Practical usage example

Returning from a route:
```php
Route::get('/users', function(){
    return User::filterAndGet(request());
});
```

> **A note on Eloquent API Resources:** If you are using Laravel 5.5, you can also use this package with Eloquent Resources new feature:
```php
Route::get('/users', function(){
    return UserResource::collection(User::filterAndGet(request()));
});
```php

## Filtering rules

Now, this is an important section. I have explained how to install a call the filter methods, but how can you actually define your filters? Well, it's rather simple for basic queries
and a little more verbose if you need to query based on related models.

### Defining the filtering columns

In your request, you simply use the column names as keys and the comparison values as, well the values.

##### Example

Let's say you want to retrieve the users from Germany. Your URI request would be like this:

```php
    http://www.example.com/users?country=Germany
```

Now you want to be more specific and you want to retrieve the users who, not only are from Germany, but also are males. Your URI would turn into something like this:

```php
    http://www.example.com/users?country=Germany&gender=Male
```

#### Query comparison operators

The above examples work only with exact matches, but you would probably need a more loose comparison like the one that >, <, LIKE and != operators offer. In order to use
this operators, you append a keyword at the end of the column name, separated by a '/' character. This separation character can be changed on the configuration file.

This is the list of the keywords and their corresponding operators:

- **start:** >= value
- **end:** <= value
- **like:** LIKE %value%
- **not:** != value

##### Example

Retrieve the users from Germany and under 30 years:

```php
    http://www.example.com/users?country=Germany&age%2Fend=30
```

In the previous example, %2F is the encoding for the '/' character.

