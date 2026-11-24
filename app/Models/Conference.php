<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Conference extends Model implements HasMedia
{
    use HasFactory, HasTranslatableFields, InteractsWithMedia;

    public array $translatable = ['title', 'venue', 'summary', 'body'];

    protected $fillable = ['title', 'slug', 'starts_on', 'ends_on', 'venue', 'summary', 'body', 'is_published'];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_published' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('files');
    }
}
