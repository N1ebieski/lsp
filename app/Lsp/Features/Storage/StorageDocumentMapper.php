<?php

declare(strict_types=1);

namespace App\Lsp\Features\Storage;

use App\Lsp\Detection\AutocompleteArgument;
use App\Lsp\Detection\DetectedArgument;
use App\Lsp\Detection\Pattern;
use App\Lsp\Features\Support\DocumentMapper;
use App\Lsp\Workspace;
use Illuminate\Support\Collection;

class StorageDocumentMapper extends DocumentMapper
{
    /**
     * Create a new storage document mapper instance.
     *
     * @param  Collection<string, array<string, mixed>>  $disks
     */
    public function __construct(
        protected Workspace $workspace,
        protected Collection $disks,
    ) {
        //
    }

    /**
     * Get storage detection patterns.
     *
     * @return array<int, Pattern>
     */
    protected function patterns(): array
    {
        return [
            Pattern::attribute(class: Pattern::containerAttribute('Storage'), argument: 0),
            Pattern::method(method: ['disk', 'fake', 'persistentFake', 'forgetDisk'], class: Pattern::facade('Storage'), argument: 0),
        ];
    }

    /**
     * Convert the given argument to document links.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function toLinks(DetectedArgument $argument): array
    {
        $disk = $this->find($argument);

        if ($disk === null || ! is_string($disk['file'] ?? null)) {
            return [];
        }

        return [
            $this->workspace->link(
                $argument->range(),
                $disk['file'],
                is_numeric($disk['line'] ?? null) ? (int) $disk['line'] : null,
            ),
        ];
    }

    /**
     * Convert the given argument to hover.
     *
     * @param  array<string, mixed>  $position
     * @return array<string, mixed>|null
     */
    protected function toHover(DetectedArgument $argument, array $position): ?array
    {
        return null;
    }

    /**
     * Convert the given argument to diagnostics.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function toDiagnostics(DetectedArgument $argument): array
    {
        $value = $argument->stringValue();

        if ($value === null || $this->disks->has($value)) {
            return [];
        }

        return [[
            'range'    => $argument->range(),
            'severity' => 2,
            'source'   => 'Laravel Extension',
            'code'     => 'storage_disk',
            'message'  => "Storage Disk [{$value}] not found.",
        ]];
    }

    /**
     * Convert the given argument to completion items.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function toCompletions(AutocompleteArgument $argument): array
    {
        return $this->disks
            ->keys()
            ->filter(fn (mixed $disk): bool => is_string($disk) && $disk !== '' && ! str_contains($disk, '.'))
            ->map(fn (string $disk): array => [
                'label'    => $disk,
                'kind'     => 12,
                'textEdit' => [
                    'range'   => $argument->replacementRange(),
                    'newText' => $disk,
                ],
            ])
            ->values()
            ->all();
    }

    /**
     * Find the storage disk for the given argument.
     *
     * @return array<string, mixed>|null
     */
    protected function find(DetectedArgument $argument): ?array
    {
        $disk = $this->disks->get($argument->stringValue());

        return is_array($disk) ? $disk : null;
    }
}
