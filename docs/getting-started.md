# Getting Started

## Requirements

- `php` version 8.3 or higher
- `composer` for package installation

## Installation

Install the package using Composer:

```sh
composer require joshmcrae/kick
```

## Configuration

The entrypoint for a Kick application is a single PHP file that should be placed
in the web server's document root (alongside any static assets). Any request that
doesn't match a static asset should be routed to this file.

For this example, we'll use `public/index.php`:

```php
<?php

require_once(__DIR__ . '/vendor/autoload.php');

use Kick\Application;
use Kick\Http\Request;

(new Application)                           // instantiate the application
    ->withRoutes(__DIR__ . '/../pages')     // specify the routes directory
    ->handle(Request::fromGlobals())        // handle the incoming request
    ->send();                               // send the response to the client
```

## Defining a route

In our example configuration, files found under the `pages/` directory will map
to URIs that are available in the application. A route file must return a closure
that accepts the incoming request as an argument and returns a response.

Let's create a file named `pages/greeting/_name.get.php`:


```php
<?php

use Kick\Http\Request;
use Kick\View\Element as e;

return fn (Request $request) => e::html(
    e::body(
        e::h1('Hello, ', ucfirst($request->get('name')), '!'),
        e::p('Welcome to Kick.')
    )
);
```

Note that we're returning an HTML document, but didn't need to call out to a
special templating language to do so. Tags are create by calling static methods
of the same name on the `Element` class.

## Trying it out

To test the application using PHP's built-in web server, run the following
command:

```sh
php -S 0.0.0.0:3000 -t public/
```

Navigate to `http://0.0.0.0:3000/greeting/kick` in the browser and replace `kick`
with different values to get different greetings.
