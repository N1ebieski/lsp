<?php

declare(strict_types=1);

namespace App\Lsp\Transport;

use App\Lsp\Contracts\Transport;
use Closure;

class StdioTransport implements Transport
{
    /**
     * The message handler callback.
     *
     * @var  (Closure(string): void)|null
     */
    protected ?Closure $handler = null;

    /**
     * Register a handler for incoming messages.
     */
    public function onReceive(Closure $handler): void
    {
        $this->handler = $handler;
    }

    /**
     * Send a message to the client with LSP Content-Length framing.
     */
    public function send(string $message): void
    {
        $length = strlen($message);

        fwrite(STDOUT, "Content-Length: {$length}\r\n\r\n{$message}");
    }

    /**
     * Start the main event loop, reading from STDIN.
     */
    public function run(): void
    {
        stream_set_blocking(STDIN, false);

        while (! feof(STDIN)) {
            $headers = $this->readHeaders();

            if ($headers === null) {
                usleep(10000);

                continue;
            }

            $contentLength = $this->parseContentLength($headers);

            if ($contentLength === null) {
                continue;
            }

            $body = $this->readBody($contentLength);

            if ($body === null) {
                continue;
            }

            if (is_callable($this->handler)) {
                ($this->handler)($body);
            }
        }
    }

    /**
     * Read LSP headers from STDIN until the blank line separator.
     */
    protected function readHeaders(): ?string
    {
        $headers = '';

        while (true) {
            $line = fgets(STDIN);

            if ($line === false) {
                return $headers === '' ? null : $headers;
            }

            $headers .= $line;

            if ($line === "\r\n") {
                return $headers;
            }
        }
    }

    /**
     * Extract the Content-Length value from the raw headers.
     */
    protected function parseContentLength(string $headers): ?int
    {
        if (preg_match('/Content-Length:\s*(\d+)/i', $headers, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Read the message body of the given length from STDIN.
     */
    protected function readBody(int $length): ?string
    {
        stream_set_blocking(STDIN, true);

        $body = fread(STDIN, $length);

        stream_set_blocking(STDIN, false);

        return $body !== false ? $body : null;
    }
}
