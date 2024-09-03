<?php

namespace Kick\Http;

readonly class Route
{
    /**
     * Regex matching the URI.
     *
     * @var string
     */
    private string $pattern;

    /**
     * Route constructor.
     *
     * @param string $method 
     * @param string $uri 
     * @param string $filename 
     * @param array $middleware 
     * @return void
     */
    public function __construct(
        public string $method,
        public string $uri,
        public string $filename,
        public array  $middleware = []
    ) {
        $pattern = str_replace('/', '\/', $uri);
        $pattern = preg_replace('/\:(\w*)/', '(?<$1>\w*)', $pattern);

        $this->pattern = '/^' . $pattern . '$/';
    }

    /**
     * Returns true if the route matches a request.
     *
     * @param Request $request
     * @param array   $segments
     * @return bool
     */

    public function matches(Request $request, array &$segments = []): bool
    {
        if ($this->method !== 'ANY' && $request->method !== $this->method) {
            return false;
        }

        $match = [];

        if (preg_match($this->pattern, $request->path, $match)) {
            $segments = array_filter($match, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }

        return false;
    }

    /**
     * Returns a pretty-printable string representation
     * of the route.
     *
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('%s %s', str_pad($this->method, 4), $this->uri);
    }
}
