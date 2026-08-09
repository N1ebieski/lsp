<?php

declare(strict_types=1);

namespace App\Lsp;

use App\Lsp\Exceptions\ProjectNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class Projects
{
    /** @var array<int, Project> */
    private readonly array $projects;

    public function __construct(Project ...$projects)
    {
        $this->projects = $projects;
    }

    /**
     * Get all projects as a collection.
     *
     * @return Collection<int, Project>
     */
    public function collect(): Collection
    {
        return collect($this->projects);
    }

    /**
     * Get first project in the collection.
     */
    public function first(): Project
    {
        return Arr::first($this->projects);
    }

    /**
     * Get the project that contains the given URI.
     *
     * @throws ProjectNotFoundException if no project is found for the given URI.
     */
    public function get(string $uri): Project
    {
        return $this
            ->collect()
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
