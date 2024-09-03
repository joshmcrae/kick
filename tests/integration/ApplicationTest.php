<?php

use Kick\Application;
use Kick\Http\Request;
use Kick\Http\Router;
use Kick\Service\Container;
use Kick\Test\ServiceB;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Application::class)]
#[CoversClass(Router::class)]
#[CoversClass(Container::class)]
class ApplicationTest extends TestCase
{
    private Application $application;

    protected function setUp(): void
    {
        $this->application = (new Application)
            ->withRoutes(__DIR__ . '/../fixtures/pages')
            ->withProvider(fn (Container $c) => 
                $c->literal(ServiceB::class, new ServiceB('foobar'))
            );
    }

    public function testHandleWithTextResponse()
    {
        $response = $this
            ->application
            ->handle(new Request('GET', '/content/text'));

        $this->assertEquals('text/plain', $response->headers['content-type']);
        $this->assertEquals('Hello, world!', $response->body);
    }

    public function testHandleWithArrayResponse()
    {
        $response = $this
            ->application
            ->handle(new Request('GET', '/content/json'));

        $this->assertEquals('application/json', $response->headers['content-type']);
        $this->assertEquals('{"foo":"bar"}', $response->body);
    }

    public function testHandleWithHtmlResponse()
    {
        $response = $this
            ->application
            ->handle(new Request('GET', '/content/html'));

        $this->assertEquals('text/html', $response->headers['content-type']);
        $this->assertEquals('<strong>Hello, world!</strong>', $response->body);
    }

    public function testDefaultExceptionHandler()
    {
        $response = $this
            ->application
            ->handle(new Request('GET', '/does-not-exist'));

        $this->assertEquals('text/html', $response->headers['content-type']);
        $this->assertEquals('<!DOCTYPE html><html><body><h1>Error</h1><p>Route does not exist.</p></body></html>', $response->body);
    }

    public function testCustomExceptionHandler()
    {
        $this
            ->application
            ->withExceptionHandler(fn ($e) => ['error' => $e->getMessage()]);

        $response = $this
            ->application
            ->handle(new Request('GET', '/does-not-exist'));

        $this->assertEquals('application/json', $response->headers['content-type']);
        $this->assertEquals('{"error":"Route does not exist."}', $response->body);
    }

    public function testHandlerDependencyInjection()
    {
        $response = $this
            ->application
            ->handle(new Request('GET', '/'));

        $this->assertEquals('text/plain', $response->headers['content-type']);
        $this->assertEquals('foobar', $response->body);
    }

    public function testResolvedMiddleware()
    {
        $response = $this
            ->application
            ->handle(new Request('GET', '/'));

        $this->assertEquals('baz', $response->headers['x-test-value']);
    }

    public function testStackedMiddleware()
    {
        $response = $this
            ->application
            ->handle(new Request('GET', '/users/123'));

        $this->assertEquals('/login', $response->headers['location']);
        $this->assertEquals('baz', $response->headers['x-test-value']);
    }
}
