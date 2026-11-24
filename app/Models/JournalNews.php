<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/** News shown on jac-kw.org (kept apart from KSAAC news). */
class JournalNews extends Model implements HasMedia
{
    use HasFactory, HasTranslatableFields, InteractsWithMedia;

    protected $table = 'journal_news';

    public array $translatable = ['title', 'excerpt', 'body'];

    protected $fillable = ['title', 'slug', 'excerpt', 'body', 'published_at', 'is_published'];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
        $this->addMediaCollection('gallery');
    }
}
