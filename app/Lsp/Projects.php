<?php

declare(strict_types=1);

namespace App\Lsp;

use App\Lsp\Exceptions\ProjectNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class Projects
{
    /** @var Project[] */
    private readonly array $projects;

    public function __construct(Project ...$projects)
    {
        $this->projects = $projects;
    }

    /**
     * Get the project that contains the given URI.
     *
     * @throws ProjectNotFoundException if no project is found for the given URI.
     */
    public function get(string $uri): Project
    {
        return Arr::first(
            $this->projects,
            fn (Project $project) => Str::startsWith($uri, (string) $project->uri)
        ) ?? throw new ProjectNotFoundException($uri);
    }
}
