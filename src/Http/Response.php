<?php

namespace Kick\Http;

class Response
{
    /**
     * Create a JSON response from a JSON-encodable value.
     *
     * @param mixed $value
     * @param int $statusCode
     * @return Response
     */
    public static function json(mixed $value, int $statusCode = 200): Response
    {
        return new Response(
            statusCode: $statusCode,
            headers: ['content-type' => 'application/json'],
            body: json_encode($value)
        );
    }

    /**
     * Response constructor.
     *
     * @param int $statusCode
     * @param array<string,string|string[]> $headers
     * @param string $body
     */
    public function __construct(
        public int $statusCode = 200,
        public array $headers = [],
        public string $body = ''
    ) {
    }

    /**
     * Sends the response to the client.
     *
     * @return void
     */
    public function send(): void
    {
        header('HTTP/1.1 ' . $this->statusCode);

        foreach ($this->headers as $name => $values) {
            if (!is_array($values)) {
                $values = [$values];
            }

            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value));
            }
        }

        echo $this->body;
    }
}
