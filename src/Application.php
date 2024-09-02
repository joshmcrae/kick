<?php

namespace Kick;

use Kick\Service\Container;

class Application
{
    /**
     * Service container.
     *
     * @var Container $container
     */
    readonly public Container $container;

    /**
     * Application constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->container = new Container;
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
}
