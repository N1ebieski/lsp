<?php

declare(strict_types=1);

namespace App\Lsp;

use App\Lsp\Exceptions\ProjectNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class Projects
{
    /** @var Collection<int, Project> */
    private readonly Collection $projects;

    public function __construct(Project ...$projects)
    {
        $this->projects = collect($projects);
    }

    /**
     * Get the project that contains the given URI.
     *
     * @throws ProjectNotFoundException if no project is found for the given URI.
     */
    public function get(string $uri): Project
    {
        return $this->projects
            ->sortByDesc(fn (Project $project): int => strlen((string) $project->uri))
            ->first(fn (Project $project): bool => $this->containsUri($project, $uri))
            ?? throw new ProjectNotFoundException($uri);
    }

    /**
     * Determine whether the given project contains the URI.
     */
    protected function containsUri(Project $project, string $uri): bool
    {
        return $uri === (string) $project->uri
            || Str::startsWith($uri, Str::finish((string) $project->uri, '/'));
    }
}
