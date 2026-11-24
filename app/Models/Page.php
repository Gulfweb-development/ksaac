<?php

namespace App\Models;

use App\Enums\Site;
use App\Models\Concerns\HasTranslatableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Page extends Model implements HasMedia
{
    use HasFactory, HasTranslatableFields, InteractsWithMedia;

    public array $translatable = ['title', 'content', 'meta_description'];

    protected $fillable = ['site', 'title', 'slug', 'content', 'meta_description', 'is_published'];

    protected function casts(): array
    {
        return [
            'site' => Site::class,
            'is_published' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')->singleFile();
    }
}
