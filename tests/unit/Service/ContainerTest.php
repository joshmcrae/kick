<?php

namespace Kick\Service;

use Kick\Test\ServiceA;
use Kick\Test\ServiceB;
use Kick\Test\ServiceD;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Container::class)]
class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container;
    }

    public function testMissing()
    {
        $this->expectException(ContainerException::class);

        $this->container->resolve('baz');
    }

    public function testLiteral()
    {
        $this->container->literal('foo', 'bar');

        $this->assertEquals('bar', $this->container->resolve('foo'));
    }

    public function testFactory()
    {
        $this->container->factory('foo', fn () => new \stdClass);
        $resolved = $this->container->resolve('foo');
        $cached = $this->container->resolve('foo');

        $this->assertInstanceOf(\stdClass::class, $resolved);
        $this->assertSame($resolved, $cached);
    }

    public function testResolveWithAutowiring()
    {
        $this->container->factory(ServiceB::class, fn () => new ServiceB('baz'));
        $resolved = $this->container->resolve(ServiceD::class);

        $this->assertInstanceOf(ServiceD::class, $resolved);
    }

    public function testInvokeWithResolvedArgs()
    {
        $callable = fn (ServiceA $serviceA, ServiceB $serviceB) => $serviceB->config;
        $provided = [new ServiceB('manually-injected')];

        $result = $this->container->invokeWithResolvedArgs($callable, $provided);
        $this->assertEquals('manually-injected', $result);
    }
}
