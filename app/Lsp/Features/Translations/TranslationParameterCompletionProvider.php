<?php

declare(strict_types=1);

namespace App\Lsp\Features\Translations;

use App\Lsp\Contracts\CompletionProvider;
use App\Lsp\Data\Translations;
use App\Lsp\Detection\AutocompleteArgument;
use App\Lsp\Detection\AutocompleteArguments;
use App\Lsp\Detection\Pattern;
use App\Lsp\Document;
use App\Lsp\Workspace;

class TranslationParameterCompletionProvider implements CompletionProvider
{
    /**
     * Create a new translation parameter completion provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide translation parameter completions for the given document and position.
     *
     * @param  array<string, mixed>  $position
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document, array $position): array
    {
        if (! $this->workspace->config->boolean('translationCompletion', true)) {
            return [];
        }

        return AutocompleteArguments::in($document, $position)
            ->matching($this->patterns())
            ->values()
            ->filter(fn (AutocompleteArgument $argument): bool => $argument->isArray() && $argument->isArrayKeyCompletion())
            ->flatMap(fn (AutocompleteArgument $argument): array => $this->toCompletions($argument, $this->workspace->data->translations()))
            ->values()
            ->all();
    }

    /**
     * Get translation parameter completion patterns.
     *
     * @return array<int, Pattern>
     */
    protected function patterns(): array
    {
        return [
            Pattern::method(method: 'get', class: Pattern::contract('Translation\\Translator'), argument: 1),
            Pattern::method(method: 'choice', class: Pattern::contract('Translation\\Translator'), argument: 2),
            Pattern::method(method: ['__', 'trans', '@lang'], argument: 1),
            Pattern::method(method: 'trans_choice', argument: 2),
            Pattern::method(method: 'get', class: Pattern::facade('Lang'), argument: 1),
            Pattern::method(method: 'choice', class: Pattern::facade('Lang'), argument: 2),
        ];
    }

    /**
     * Convert the given argument to completion items.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function toCompletions(AutocompleteArgument $argument, Translations $translations): array
    {
        $translationKey = $argument->stringValueAt(0);

        if ($translationKey === null) {
            return [];
        }

        $translation = $translations->translations()->get(str_replace('\\', '', $translationKey));

        if (! is_array($translation)) {
            return [];
        }

        $item = $this->defaultTranslation($translation, $translations);

        if (! is_array($item)) {
            return [];
        }

        return collect($item['params'] ?? [])
            ->filter(fn (mixed $parameter): bool => is_string($parameter) && $parameter !== '')
            ->map(fn (string $parameter): array => [
                'label'    => $parameter,
                'kind'     => 6,
                'textEdit' => [
                    'range'   => $argument->replacementRange(),
                    'newText' => $parameter,
                ],
            ])
            ->values()
            ->all();
    }

    /**
     * Get the default translation item, falling back to the first locale.
     *
     * @param  array<string, array<string, mixed>>  $translation
     * @return array<string, mixed>|null
     */
    protected function defaultTranslation(array $translation, Translations $translations): ?array
    {
        $item = $translation[$translations->defaultLocale()] ?? reset($translation);

        return is_array($item) ? $item : null;
    }
}
