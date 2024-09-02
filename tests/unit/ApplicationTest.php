<?php

namespace Kick;

use Kick\Service\Container;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Application::class)]
class ApplicationTest extends TestCase
{
    private Application $application;

    protected function setUp(): void
    {
        $this->application = (new Application)
            ->withProvider(fn (Container $c) => $c->literal('foo', 'baz'));
    }

    public function testProviderInvoked()
    {
        $this->assertEquals('baz', $this->application->container->resolve('foo'));
    }
}
