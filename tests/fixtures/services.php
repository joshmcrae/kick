<?php

namespace Kick\Test;

use Kick\Http\Request;

class ServiceA {}

class ServiceB
{
    public function __construct(public string $config) {}
}


class ServiceC
{
    public function __construct(public ServiceA $serviceA, public ServiceB $serviceB) {}
}

class ServiceD
{
    public function __construct(public ServiceC $serviceC) {}
}

class AddHeaderMiddleware
{
    public function __invoke(Request $request, callable $next)
    {
        $response = $next($request);
        $response->headers['x-test-value'] = 'baz';

        return $response;
    }
}
