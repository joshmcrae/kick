# Views

The `Kick\View\Element` class is responsible for generating HTML directly from
PHP code. Instead of calling out to a separate templating language, views can
be composed through the use of PHP functions, classes, loops and conditionals.

```php
<?php

use Kick\View\Element as e;

return fn () => e::html(
    e::body(
        e::h1('A complete document'),
        e::p('Directly from PHP.')
    )
);
```

## HTML tags

Calling a static method on the `Element` class will return an HTML tag of the
same name. Positional arguments are treated as children of the tag, while named
arguments are treated as attributes. Plain strings passed as children are escaped
to prevent XSS.

```php
e::form(
    e::input(name: 'email', type: 'email', placeholder: 'you@example.com'),
    e::button('Submit'),
    action: '/subscribe',
    method: 'POST'
);
```

Underscores in attribute names are automatically converted to hyphens. In some
cases, more complex attribute names are needed. For example, you may be using
a front end library that understands attributes in the form of `:bind` and `@click`.
In such cases, you can use the `Element::attr()` method in a positional argument
to specify a literal attribute name:

```php
e::button('Load more...', e::attr('@click', 'loadMore()'));
```

## Layouts and components

Because views are just PHP code, languages features can be leveraged to create
abstractions like layouts and components.

```php
use Kick\Element\View as e;

class Layout
{
    public static function dashboard(string $title, ...$args)
    {
        return e::html(
            e::head(
                e::script(src: 'https://cdn.com/library.js'),
                e::title($title . ' - Acme Dashboard')
            ),
            e::body(...$args)
        );
    }
}
```

```php
use Kick\Element\View as e;

class Form
{
    public static function input(string $name, string $label)
    {
        return e::div(
            e::label($label, for: 'field-' . $name),
            e::input(name: $name, id: 'field-' . $name)
        );
    }
}
```

Functional programming can also help to compose views in an expressive way.

```php
use Kick\Element\View as e;

$list = fn ($items) => e::ul(
    array_map(($i) => e::li($i), $items)
);
```

## Adding client-side functionality

Kick is suited to applications that follow a "traditional" model of the web
where views are rendered on the server before being sent to the client. While
Kick can absolutely be used to implement a JSON API for a single-page-application
(SPA), not all projects call for this level of functionality (or complexity).

In cases where you wish to progressively enhance the user experience, or need
features that can only be offered through the browser via JavaScript, one of
the following libraries may be useful:

- [HTMX](htmx.org)
- [AlpineJS](alpinejs.dev)
