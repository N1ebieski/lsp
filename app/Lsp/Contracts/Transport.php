<?php

declare(strict_types=1);

namespace App\Lsp\Contracts;

use Closure;

interface Transport
{
    /**
     * Register a handler for incoming messages.
     *
     * @param  (Closure(string): void)  $handler
     */
    public function onReceive(Closure $handler): void;

    /**
     * Start listening for incoming messages.
     */
    public function run(): void;

    /**
     * Send a message to the client.
     */
    public function send(string $message): void;
}
