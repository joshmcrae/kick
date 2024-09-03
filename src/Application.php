<?php

namespace Kick;

use Kick\Http\Request;
use Kick\Http\Response;
use Kick\Http\Route;
use Kick\Http\Router;
use Kick\Service\Container;
use Kick\View\Element as e;

class Application
{
    /**
     * Service container.
     *
     * @var Container
     */
    readonly public Container $container;

    /**
     * Request router.
     *
     * @var Router
     */
    readonly public Router $router;

    /**
     * Called when an uncaught exception is raised.
     *
     * @var callable
     */
    private mixed $exceptionHandler;

    /**
     * Application constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->container = new Container;
        $this->router = new Router('');
        $this->exceptionHandler = fn (\Throwable $e, Request $r): Response => $this->handleError($e, $r);
    }

    /**
     * Registers a service provider with the application.
     *
     * @param callable $provider
     * @return $this
     */
    public function withProvider(callable $provider): self
    {
        call_user_func($provider, $this->container);

        return $this;
    }

    /**
     * Sets the routes directory.
     *
     * @param string $path
     * @return $this
     */
    public function withRoutes(string $path): self
    {
        $this->router->path = $path;

        return $this;
    }

    /**
     * Replaces the default exception handler.
     *
     * @param callable $handler
     * @return $this
     */
    public function withExceptionHandler(callable $handler): self
    {
        $this->exceptionHandler = $handler;

        return $this;
    }

    /**
     * Handles a request and returns its response.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        $route = null;

        if ($this->router->path !== '') {
            $route = $this->router->match($request, $request->segments);
        }

        try {
            if (!$route) {
                throw new ApplicationException(
                    'Route does not exist.',
                    ApplicationException::NOT_FOUND
                );
            }

            $stack = $this->buildHandlerStack($route);
            return $stack($request);
        } catch (\Throwable $e) {
            return $this->toResponse(call_user_func($this->exceptionHandler, $e, $request));
        }
    }

    /**
     * Returns a closure that invokes a route handler through the entire stack of
     * middleware defined for it.
     *
     * @param Route $route
     * @return callable
     */
    private function buildHandlerStack(Route $route): callable
    {
        // Call the handler as the final step
        $handler = require($this->router->path . $route->filename);
        $fn = fn (Request $request): Response => $this->toResponse(
            $this
                ->container
                ->invokeWithResolvedArgs($handler, [$request])
        );

        // Work backwards through defined middleware to wrap the handler
        $middleware = $route->middleware;
        while ($filename = array_pop($middleware)) {
            $definition = require($this->router->path . $filename);

            // Cloures can be invoked directly
            if (is_callable($definition)) {
                $fn = fn (Request $request): mixed => $this
                    ->container
                    ->invokeWithResolvedArgs($definition, [$request, $fn]);

                continue;
            }

            if (!is_array($definition)) {
                throw new ApplicationException(
                    'Middleware must be a callable or array of container service names'
                );
            }

            // Resolve invokables from container
            while ($name = array_pop($definition)) {
                $service = $this
                    ->container
                    ->resolve($name);

                $fn = fn (Request $request): mixed => $this
                    ->container
                    ->invokeWithResolvedArgs($service, [$request, $fn]);
            }
        }

        return $fn;
    }

    /**
     * Returns an HTTP response for a handler result.
     *
     * @param mixed $result
     * @return Response
     */
    private function toResponse(mixed $result): Response
    {
        return match (true) {
            $result instanceof Response => $result,
            $result instanceof e => new Response(headers: ['content-type' => 'text/html'], body: (string) $result),
            is_array($result), is_object($result) => Response::json($result),
            default => new Response(headers: ['content-type' => 'text/plain'], body: (string) $result)
        };
    }

    /**
     * Handles an exception caught by the application in accordance
     * with the request's content type.
     *
     * @param \Throwable $exception
     * @param Request $request
     * @return Response
     */
    private function handleError(\Throwable $exception, Request $request): Response
    {
        $statusCode = 500;
        $message = 'An unexpected error occurred.';

        if ($exception instanceof ApplicationException) {
            $statusCode = $exception->getCode();
            $message = $exception->getMessage();
        }

        $contentType = 
            $request->headers['accept'] ?? 
            $request->headers['content-type'] ??
            'text/html';

        return match($contentType) {
            'application/json' => Response::json($message, $statusCode),
            'text/plain' => new Response(
                $statusCode, 
                ['content-type' => 'text/plain'], 
                $message
            ),
            default => new Response(
                $statusCode, 
                ['content-type' => 'text/html'],
                (string) e::html(e::body(e::h1('Error'), e::p($message))
                )
            )
        };
    }
}
