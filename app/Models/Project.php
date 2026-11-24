<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Project extends Model implements HasMedia
{
    use HasFactory, HasTranslatableFields, InteractsWithMedia;

    public array $translatable = ['title', 'summary', 'body'];

    protected $fillable = ['title', 'slug', 'summary', 'body', 'started_on', 'is_featured', 'is_published'];

    protected function casts(): array
    {
        return [
            'started_on' => 'date',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }
}
