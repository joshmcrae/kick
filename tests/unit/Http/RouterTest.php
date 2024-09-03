<?php

namespace Kick\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Router::class)]
class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router(__DIR__ . '/../../fixtures/pages');
    }

    public function testGetRoutes()
    {
        $routes = $this->router->getRoutes();

        $this->assertCount(6, $routes);
    }

    public function testMatchWithWrongMethod()
    {
        $post = new Request('POST', '/');
        $get = new Request('GET', '/');

        $this->assertNull($this->router->match($post));
        $this->assertInstanceOf(Route::class, $this->router->match($get));
    }

    public function testMatchDefinedRoute()
    {
        $request = new Request('GET', '/posts/123');
        $route = $this->router->match($request, $request->segments);

        $this->assertEquals(['pid' => '123'], $request->segments);
        $this->assertEquals('/posts/_pid/index.php', $route->filename);
        $this->assertEquals(['/__middleware.php'], $route->middleware);
    }

    public function testMatchMultipleMiddleware()
    {
        $request = new Request('GET', '/users/123');
        $route = $this->router->match($request, $request->segments);

        $this->assertEquals(['/__middleware.php', '/users/__middleware.php'], $route->middleware);
    }
}
