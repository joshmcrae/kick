# Dependency Injection

Dependency injection is an important tool in building maintainable object-oriented
codebases. Instead of leaving it up to each class to create and configure its
dependencies, a service container _injects_ those dependencies into classes as
they are being instantiated.

To demonstrate this concept, consider a class that needs a database connection to
do something useful.

```php
<?php

class RegisterUser
{
    public function __construct(private Database $db) {}

    public function __invoke(string $username, string $password)
    {
        $this
            ->db
            ->insert('users', [
                'username' => $username,
                'pw_hash' => password_hash($password, PASSWORD_DEFAULT)
            ]);
    }
}
```

Here the constructor accepts an instance of the `Database` class instead of
creating one itself. This allows a single database connection to be used
throughout the application, and allows different configurations of the class
(or interface) to be injected in different environments, such as testing.

## Literals and Factories

In cases where Kick knows how to create all dependencies of a class, it will
automatically construct the class when it needs to be resolved. This is called
auto-wiring, and it enables quicker development while cutting down on manual
configuration.

In some cases however, Kick cannot determine all dependencies. Take the `Database`
from the earlier example. We would typically require a host and some credentials
to open a connection. In this case, we need to instruct the service container on
how to instantiate the connection and we can do this in one of two ways.


### Literal

A literal service definition simply binds a service name (typically a class' or
interface's FQN) to a value. This is useful for giving meaningful names to static
values, or values coming from the environment.

```php
$container->literal('dsn', 'sqlite::memory:');
$container->literal('apiKey', getenv('API_KEY'));
```

### Factory

A factory service definition on the other hand binds a service name to a `callable`
that is responsible for building the service on demand. This is useful in situations
where the service isn't required on every request and you want it constructed lazily.

```php
$container->factory(Database::class, fn () => new Database($container->resolve('dsn')));
```

Note that once resolved, an object created by a factory is cached and returned on
subsequent requests for that service.

## Providers

A Kick application can be configured with any number of service providers, tasked
with defining base depenendencies and performing other initialization during boot.
A service provider is simply a `callable` that receives an instance of `Kick\Service\Container`.

```php
<?php

use Kick\Service\Container;
use Kick\Application;

$app = (new Application)
    ->withProvider(fn (Container $c) => $c
        ->literal('db_dsn', 'sqlite::memory:)
        ->factory(Database::class, fn () => new Database($c->resolve('db_dsn')))
    );
```

## Resolving Services

To resolve a service, `Container::resolve()` can be called with the desired
service name. This should only be necessary within a service provider, because
Kick will automatically resolve dependencies when invoking route handlers and
middleware. Any type-hinted parameter specified on a route handler or middleware
closure will be auto-injected by the service container.

```php
<?php

use Kick\Http\Request;

return fn (Request $request, Database $db) =>
    $db
        ->query('select * from posts where uid = ?', $request->get('uid'))
        ->fetchAll();
```
