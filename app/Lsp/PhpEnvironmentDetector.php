<?php

declare(strict_types=1);

namespace App\Lsp;

use Throwable;

class PhpEnvironmentDetector
{
    /**
     * Create a new PHP environment detector instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {}

    /**
     * Resolve the PHP command for the configured environment.
     *
     * @return string[]
     */
    public function command(): array
    {
        return match ($this->workspace->config->phpEnvironment()) {
            'herd'  => $this->herd(),
            'valet' => $this->valet(),
            'sail'  => $this->sail(),
            'lando' => $this->lando(),
            'ddev'  => $this->ddev(),
            'local' => $this->local(),
            'auto'  => $this->auto(),
            default => ['php'],
        };
    }

    /**
     * Auto-detect the PHP command.
     *
     * @return string[]
     */
    protected function auto(): array
    {
        foreach (['herd', 'valet', 'sail', 'lando', 'ddev', 'local'] as $environment) {
            $command = $this->{$environment}();

            if ($command !== ['php']) {
                return $command;
            }
        }

        return ['php'];
    }

    /**
     * Resolve the Herd PHP command.
     *
     * @return string[]
     */
    protected function herd(): array
    {
        $output = $this->run(['herd', 'which-php']);

        if ($output === null || str_contains($output, 'No usable PHP version found')) {
            return ['php'];
        }

        return $this->binaryCommand($output);
    }

    /**
     * Resolve the Valet PHP command.
     *
     * @return string[]
     */
    protected function valet(): array
    {
        return $this->binaryCommand($this->run(['valet', 'which-php']));
    }

    /**
     * Resolve the Sail PHP command.
     *
     * @return string[]
     */
    protected function sail(): array
    {
        return $this->run(['./vendor/bin/sail', 'ps']) === null
            ? ['php']
            : ['./vendor/bin/sail', 'php'];
    }

    /**
     * Resolve the Lando PHP command.
     *
     * @return string[]
     */
    protected function lando(): array
    {
        return $this->run(['lando', 'php', '-r', 'echo PHP_BINARY;']) === null
            ? ['php']
            : ['lando', 'php'];
    }

    /**
     * Resolve the DDEV PHP command.
     *
     * @return string[]
     */
    protected function ddev(): array
    {
        return $this->run(['ddev', 'php', '-r', 'echo PHP_BINARY;']) === null
            ? ['php']
            : ['ddev', 'php'];
    }

    /**
     * Resolve the local PHP command.
     *
     * @return string[]
     */
    protected function local(): array
    {
        return $this->binaryCommand($this->run(['php', '-r', 'echo PHP_BINARY;']));
    }

    /**
     * Convert a detected PHP binary path into a command.
     *
     * @return string[]
     */
    protected function binaryCommand(?string $output): array
    {
        $binary = trim((string) $output);

        return $binary === '' ? ['php'] : [$binary];
    }

    /**
     * Run a command in the workspace path.
     *
     * @param  string[]  $command
     */
    protected function run(array $command): ?string
    {
        try {
            $process = @proc_open($command, [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ], $pipes, $this->workspace->path());

            if (!is_resource($process)) {
                return null;
            }

            $output = stream_get_contents($pipes[1]);

            fclose($pipes[1]);
            fclose($pipes[2]);

            return proc_close($process) === 0 && $output !== false ? $output : null;
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }
}
