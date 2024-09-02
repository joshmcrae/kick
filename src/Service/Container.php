<?php

namespace Kick\Service;

class Container
{
    /**
     * Resolved services.
     *
     * @var array<string,mixed>
     */
    private array $services = [];

    /**
     * Service factories.
     *
     * @var array<string,callable>
     */
    private array $factories = [];

    /**
     * Binds a literal value to a service name.
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function literal(string $name, mixed $value): self
    {
        $this->services[$name] = $value;

        return $this;
    }

    /**
     * Registers a factory for resolving a service.
     *
     * @param string $name
     * @param callable $factory
     * @return $this
     */
    public function factory(string $name, callable $factory)
    {
        $this->factories[$name] = $factory;

        return $this;
    }

    /**
     * Resolves a service by name.
     *
     * @param string $name
     * @return mixed
     */
    public function resolve(string $name, ?string $resolving = null): mixed
    {
        if (isset($this->services[$name])) {
            return $this->services[$name];
        }

        if (isset($this->factories[$name])) {
            return $this->services[$name] = $this->factories[$name]();
        }

        if (!class_exists($name)) {
            throw ContainerException::notDefined($name, $resolving);
        }

        // Auto-wire
        $class = new \ReflectionClass($name);
        $constructor = $class->getConstructor();

        if (!$constructor) {
            return $class->newInstance();
        }

        $args = [];

        foreach ($constructor->getParameters() as $param) {
            if (!$param->getType()) {
                throw ContainerException::unknownType($param->getName(), $name);
            }

            $args[$param->getName()] = $this->resolve((string) $param->getType());
        }

        return $class->newInstanceArgs($args);
    }

    /**
     * Invokes a callable with arguments resolved from the container
     * and provided values.
     *
     * @param callable $callable
     * @param mixed[] $provided
     * @return mixed
     */
    public function invokeWithResolvedArgs(callable $callable, array $provided = []): mixed
    {
        if ($callable instanceof \Closure) {
            $function = new \ReflectionFunction($callable);
            $params = $function->getParameters();
        } elseif (is_object($callable)) {
            $object = new \ReflectionClass($callable);
            $params = $object->getMethod('__invoke')->getParameters();
        } else {
            throw ContainerException::unsupportedCallable($callable);
        }

        $args = [];

        foreach ($params as $param) {
            if (!$param->getType()) {
                throw ContainerException::unknownType($param->getName(), 'callable');
            }

            $typeName = (string) $param->getType();

            // Search provided values first
            foreach ($provided as $value) {
                if (!is_object($value)) {
                    continue;
                }

                if (get_class($value) === \Closure::class && $typeName === 'callable') {
                    $args[$param->getName()] = $value;
                    continue 2;
                }

                if (get_class($value) === $typeName) {
                    $args[$param->getName()] = $value;
                    continue 2;
                }
            }

            // Resolve from container
            $args[$param->getName()] = $this->resolve($typeName);
        }

        /** @psalm-suppress TooManyArguments */
        return call_user_func_array($callable, $args);
    }
}
