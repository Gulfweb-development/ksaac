<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableFields;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/** Awards and recognitions shown in the journal homepage carousel. */
class Award extends Model implements HasMedia
{
    use HasTranslatableFields, InteractsWithMedia;

    public array $translatable = ['title', 'description'];

    protected $fillable = ['title', 'description', 'year', 'url', 'is_active', 'sort'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }
}
