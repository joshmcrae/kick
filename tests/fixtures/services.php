<?php

namespace Kick\Test;

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
