<?php

namespace Kick\Service;

class ContainerException extends \Exception
{
    /**
     * Creates a service not defined exception.
     *
     * @return self
     */
    public static function notDefined(string $name, ?string $resolving)
    {
        $message = sprintf('Service "%s" is not defined', $name);

        if (!is_null($resolving)) {
            $message .= sprintf(' (resolving "%s")', $resolving);
        }

        $message .= '.';

        return new self($message);
    }

    /**
     * Creates an unknown parameter type exception.
     *
     * @return self
     */
    public static function unknownType(string $param, string $resolving)
    {
        $message = sprintf('Parameter "%s" has an unknown type (resolving "%s").', $param, $resolving);

        return new self($message);
    }

    /**
     * Creates an unsupportedCallalbe exception.
     *
     * @return self
     */
    public static function unsupportedCallable(callable $callable)
    {
        $name = match (true) {
            is_string($callable) => $callable,
            is_array($callable) => sprintf('%s:%s', strval($callable[0]), $callable[1]),
            is_object($callable) => get_class($callable)
        };

        return new self(sprintf('Callable "%s" is unsupported.', $name));
    }
}
