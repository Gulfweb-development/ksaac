<?php

namespace App\Models;

use App\Enums\Site;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Page extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    public array $translatable = ['title', 'content', 'meta_description'];

    protected $fillable = ['site', 'title', 'slug', 'content', 'meta_description', 'is_published'];

    protected function casts(): array
    {
        return [
            'site' => Site::class,
            'is_published' => 'boolean',
            // 'array' makes attributesToArray() hand Filament both locales, so title.ar / title.en fields fill on edit.
            'title' => 'array',
            'content' => 'array',
            'meta_description' => 'array',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')->singleFile();
    }
}
