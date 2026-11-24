<?php

namespace App\Models;

use App\Enums\ArticleType;
use App\Models\Concerns\HasTranslatableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Article extends Model implements HasMedia
{
    use HasFactory, HasTranslatableFields, InteractsWithMedia;

    public array $translatable = ['title', 'abstract', 'keywords'];

    protected $fillable = ['issue_id', 'type', 'language', 'title', 'slug', 'authors', 'abstract', 'keywords', 'page_from', 'page_to', 'doi', 'is_published', 'sort'];

    protected function casts(): array
    {
        return [
            'type' => ArticleType::class,
            // [{name: {ar, en}, affiliation: {ar, en}}, ...]
            'authors' => 'array',
            'page_from' => 'integer',
            'page_to' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    /** Author names in one language, comma separated (falls back to the other language). */
    public function authorNames(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return collect($this->authors ?? [])
            ->map(fn (array $author) => ($author['name'][$locale] ?? null) ?: collect($author['name'] ?? [])->filter()->first())
            ->filter()
            ->join($locale === 'ar' ? '، ' : ', ');
    }

    public function pages(): ?string
    {
        return $this->page_from ? $this->page_from.($this->page_to ? '–'.$this->page_to : '') : null;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')->singleFile();
    }
}
