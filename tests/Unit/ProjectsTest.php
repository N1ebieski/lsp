<?php

use App\Lsp\Exceptions\ProjectNotFoundException;
use App\Lsp\Project;
use App\Lsp\ProjectIndex;
use App\Lsp\Projects;
use App\Lsp\ScriptRunner;
use App\Lsp\Support\FileUri;
use Illuminate\Container\Container;

function makeProject(string $uri): Project
{
    return new Project(
        FileUri::of($uri),
        [],
        new ProjectIndex(new Container),
        new ScriptRunner('', []),
    );
}

test('gets the project with the longest matching URI', function () {
    $firstProject = makeProject('file:///my-first-project');
    $secondProject = makeProject('file:///my-first-project-and-something-else');

    $projects = new Projects($firstProject, $secondProject);

    expect($projects->get('file:///my-first-project-and-something-else/my-file.php'))
        ->toBe($secondProject);
});

test('gets the most specific project when projects are nested', function () {
    $parentProject = makeProject('file:///projects');
    $nestedProject = makeProject('file:///projects/application');

    $projects = new Projects($nestedProject, $parentProject);

    expect($projects->get('file:///projects/application/app/Models/User.php'))
        ->toBe($nestedProject);
});

test('gets a project when URI matches exactly', function () {
    $project = makeProject('file:///projects/application');

    $projects = new Projects($project);

    expect($projects->get('file:///projects/application'))
        ->toBe($project);
});

test('does not match a project URI that is only a path prefix', function () {
    $project = makeProject('file:///my-first-project');

    $projects = new Projects($project);

    expect(fn () => $projects->get('file:///my-first-project-archive/my-file.php'))
        ->toThrow(ProjectNotFoundException::class);
});
