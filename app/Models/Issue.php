<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Issue extends Model implements HasMedia
{
    use HasFactory, HasTranslatableFields, InteractsWithMedia;

    public array $translatable = ['title', 'theme'];

    protected $fillable = ['volume_id', 'number', 'title', 'theme', 'published_on', 'is_current', 'is_published'];

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'published_on' => 'date',
            'is_current' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Only one issue is "current" (the one subscribers unlock).
        static::saved(function (Issue $issue) {
            if ($issue->is_current && $issue->wasChanged('is_current')) {
                static::whereKeyNot($issue->getKey())->where('is_current', true)->update(['is_current' => false]);
            }
        });
    }

    public function volume(): BelongsTo
    {
        return $this->belongsTo(Volume::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class)->orderBy('sort');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('file')->singleFile();
    }
}
