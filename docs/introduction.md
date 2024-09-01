# What is Kick?

Kick is a micro framework for building web applications in modern PHP. It provides
request routing, dependency injection and view rendering in a single thoughtfully
designed package.

- **Filesystem-based routing:** route handlers are defined in files that live in
your configured routes directory. The structure of this directory maps directly to
the URIs available in your application, so there's no need for manual configuration.
- **Auto-wiring service container:** constructor arguments are automatically resolved
by the service container when building objects needed by your application. Route handler
and middleware arguments are also resolved automatically.
- **View composition from PHP:** the view layer enables generation of HTML directly
from your application code. Reusable components are achieved by bundling repeated
patterns into plain-old PHP functions or classes.
