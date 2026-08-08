<?php

declare(strict_types=1);

namespace App\Lsp;

use App\Lsp\Exceptions\ProjectNotFoundException;
use Closure;
use Revolt\EventLoop\FiberLocal;

final class ProjectContext
{
    private FiberLocal $current;

    private ?Project $default = null;

    public function __construct()
    {
        $this->current = new FiberLocal(static fn (): ?Project => null);
    }

    public function run(?Project $project, Closure $callback): mixed
    {
        $this->current->set($project);

        try {
            return $callback();
        } finally {
            $this->current->unset();
        }
    }

    public function setDefault(Project $project): void
    {
        $this->default = $project;
    }

    public function current(): Project
    {
        return $this->current->get()
            ?? $this->default
            ?? throw new ProjectNotFoundException;
    }
}
