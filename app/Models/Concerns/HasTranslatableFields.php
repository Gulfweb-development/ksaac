<?php

namespace App\Models\Concerns;

use Spatie\Translatable\HasTranslations;

/**
 * Spatie translations + an 'array' cast on every translatable column.
 * The cast makes attributesToArray() hand Filament both locales, so title.ar / title.en fields fill on edit.
 * Models list their columns in `public array $translatable`.
 */
trait HasTranslatableFields
{
    use HasTranslations;

    public function initializeHasTranslatableFields(): void
    {
        $this->mergeCasts(array_fill_keys($this->translatable, 'array'));
    }
}
