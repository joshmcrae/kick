# Routing

Kick uses filesystem-based routing to map files under your configured routes
directory to available URIs.

## Configuration

Use the `Application::withRoutes()` method to specify the location of your
routes directory when instantiating the application. See [Getting Started](/getting-started)
for an example.

## Naming conventions

The names of route files and paths will alter their behaviour in different ways:

- Files named `__middleware.php` specify middleware for the current and child
paths. Middleware is discussed later in this section.
- Files named `index.php` map to the resource `/` in a given path.
- Names prefixed with an underscore define dynamic URI segments. As an example,
`/posts/_pid/comments.php` would expand to the URI `/posts/:pid/comments`.
- Using a file extension prefixed with an HTTP method restricts that route to
the specified method. As an example, `/dashboard.get.php` will only be accessible
by an HTTP `GET` request.

## Handlers

Route handlers are simply closures that take a `Request` object and return
a primitive PHP type, `Element` or `Response` object. Whatever the response, the
application will automatically translate it into a `Request` with appropriate
`Content-Type` header. Route files must return a handler.

```php
<?php

use Kick\Http\Request;
use Kick\View\Element as e;

return fn (Request $request) => e::p('Hello, world!');
```

The `Request` object will be injected automatically, and any other type-hinted
parameters will also be injected by the service container. To get the value of a
dynamic path segment, the `Request::$segments` array can be used, or a call to
`Request::get()` with the segment name will return its value.

## Middleware

A `__middleware.php` file must return a closure or an array of container service
names that resolve to `callable`. Middleware are very similar to route handlers,
but in addition to a `Request` object they are also passed a `callable` to be
invoked before or after the middleware has done its work. This `callable` executes
the next middleware in the stack, or the route handler if all middleware have been
invoked, returning the resulting `Response`,

The following middleware uses a hypothetical `Auth` class to protect routes from 
unauthorized users.

```php
<?php

use Kick\Http\Request;

return function (Request $request, callable $next, Auth $auth) {
    if (!$auth->hasActiveSession() && $request->path !== '/login') {
        return new Response(302, ['location' => '/login']);
    }

    return $next($request);
};
```

Note that the result of the `$next` callable is returned. Middleware can also modify
responses by doing work after the callable is invoked. Middleware encountered earlier
in the path are executed before, or "around", middleware encountered later in the path.
