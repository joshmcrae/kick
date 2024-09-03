<?php

namespace Kick\Http;

class Request
{
    /**
     * Creates a request from PHP's $_SERVER superglobal and input stream.
     *
     * @param array $values
     * @return self
     */
    public static function fromGlobals(array $values = []): self
    {
        if (empty($values)) {
            $values = $_SERVER;
        }

        // Parse path and query string parameters
        $uri = parse_url($values['REQUEST_URI']);
        assert(is_array($uri));

        $path = $uri['path'] ?? '/';
        $query = [];
        parse_str($uri['query'] ?? '', $query);

        // Parse headers
        $headers = [];
        foreach ($values as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $key = substr($key, 5);
                $key = str_replace('_', '-', strtolower($key));
                $headers[$key] = $value;
            }
        }

        // Parse body according to the Content-Type header
        $body = file_get_contents('php://input');
        $data = [];

        switch ($headers['content-type'] ?? null) {
            case 'application/json':
                if (is_string($body)) {
                    $data = json_decode($body ?: '', true);
                }
                break;
            default:
                if (is_string($body)) {
                    parse_str($body, $data);
                }
                break;
        }

        return new self(
            method: $values['REQUEST_METHOD'],
            path: $path,
            query: $query,
            headers: $headers,
            body: is_string($body) ? $body : '',
            data: $data
        );
    }

    /**
     * Request constructor.
     *
     * @param string $method 
     * @param string $path 
     * @param array $segments 
     * @param array $query 
     * @param array $headers 
     * @param string $body 
     * @param array $data 
     * @return void
     */
    public function __construct(
        public string $method,
        public string $path,
        public array  $segments = [],
        public array  $query = [],
        public array  $headers = [],
        public string $body = '',
        public array  $data = []
    ) {}

    /**
     * Returns the value of a named data property, query string parameter or 
     * route segments, returning a default if not found.
     *
     * @param string $key
     * @param mixed  $value
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return 
            $this->data[$name] ??
            $this->query[$name] ??
            $this->segments[$name] ??
            $default;
    }
}
