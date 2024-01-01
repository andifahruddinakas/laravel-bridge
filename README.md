# Laravel Bridge
<p align="center"><img src="docs/logo.svg"></p>

## Installation

Add Presenter to your composer.json file:

```json
"require": {
    "akas/laravel-bridge": "^2.0.0"
}
```

> Require `illuminate/translation` when using Pagination. 

Now, run a composer update on the command line from the root of your project:

```
composer update
```

> **NOTICE**: NOT support Laravel 5.4.*

## How to use

setup

```php
use Akas\LaravelBridge\Laravel;

require __DIR__.'/vendor/autoload.php';

$connections = [
    'default' => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'port'      => 3306,
        'database'  => 'forge',
        'username'  => 'forge',
        'password'  => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
        'strict'    => false,
        'engine'    => null,
    ],
];

Laravel::run([
        'basePath' => FCPATH
    ])
    ->setupDatabase($connections);
```

eloquent

```php
class User extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = [
       'name',
       'email',
       'password',
   ];
}

dd(User::all());
```

view

view.blade.php

```php
@foreach ($rows as $row)
    {{ $row }};
@endforeach
```

view

```php
echo View::make('view', ['rows' => [1, 2, 3]]);
```

### Example

[Laraigniter](https://github.com/akas/laraigniter)
