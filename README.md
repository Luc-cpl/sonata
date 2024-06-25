# Sonata for Orkestra

Sonata is the base package for the persistence layer in [Orkestra](https://packagist.org/packages/luccpl/orkestra), providing essential functionalities for authentication, session management, and optionally, database interactions through Doctrine ORM.

## Installation

To install Sonata, use Composer:

```bash
composer require luccpl/sonata
```

## Configuration

### Session Management

Sonata provides a session interface that currently supports PHP's default session handling. To use it, register the `Sonata\SessionProvider` in your providers list.

### Authentication Configuration

To configure authentication guards, add the `sonata.auth_guards` configuration. This configuration maps guard keys to their respective drivers and repositories:

```php
$app->config()->set('sonata.auth_guards', [
    'guard_key' => [
        'driver' => Sonata\Sessions\PHPSession::class,  // An implementation of Sonata\Interfaces\AuthGuardInterface
        'repository' => YourRepository::class,  // An implementation of Sonata\Interfaces\Repository\IdentifiableInterface
    ],
]);
```

Set the default authentication guard using the `sonata.default_guard` configuration:

```php
['sonata.default_guard', 'guard_key'];
```

#### Custom Session Interface

Optionally, you can change the session interface by setting the `sonata.session` configuration:

```php
['sonata.session', YourCustomSessionInterface::class];
```

### Added Middleware

With the session provider registered, you gain access to the `auth` middleware, which can be used to protect routes with the existing guards:

```php
return function (RouterInterface $router): void {
    $router->get('/protected', function ($request, $response) {
        return ['message' => 'This is a protected route'];
    })->middleware('auth');
};
```

optionally you can specify a guard key to use a specific guard:
<!-- @todo review this code, it is not compatible with current middleware function but should be implemented for orkestra 1.1 -->
```php
return function (RouterInterface $router): void {
    $router->get('/protected', function ($request, $response) {
        return ['message' => 'This is a protected route'];
    })->middleware('auth', ['guard' => 'web']);
};
```


## Optional: Doctrine ORM Integration

If you wish to use Doctrine ORM for database interactions, install the necessary Doctrine packages:

```bash
composer require doctrine/orm doctrine/dbal doctrine/migrations symfony/cache
```

Then add the `Sonata\Doctrine\DoctrineProvider` to the Orkestra providers list.


#### Default Configuration

By default, Doctrine is configured to use SQLite. You can configure it to use other databases supported by Doctrine by setting the `doctrine.connection` configuration in Orkestra following the [Doctrine configuration format](https://www.doctrine-project.org/projects/doctrine-dbal/en/4.0/reference/configuration.html).

```php
[
	'doctrine.connection' => [
		'dbname' => 'mydb',
		'user' => 'user',
		'password' => 'secret',
		'host' => 'localhost',
		'driver' => 'pdo_mysql',
	],
];
```

#### Entities and Migrations

You can specify the directories for your entities and migration classes using the `doctrine.entities` and `doctrine.migrations` configurations:

```php
[
	'doctrine.entities' => [
		'App\Entities' => '/path/to/entities'
	],
	'doctrine.migrations' => [
		'/path/to/migrations'
	],
];
```

By default, Sonata will use the `App\Entities` namespace for entities and the `App\Migrations` namespace for migrations in `./migrations` directory.
