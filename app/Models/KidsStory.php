<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class KidsStory extends Model implements HasMedia
{
    use HasFactory, HasTranslatableFields, InteractsWithMedia;

    protected $table = 'kids_stories';

    public array $translatable = ['title', 'description'];

    protected $fillable = ['title', 'description', 'is_published', 'sort'];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        // The story's illustrated pages, in reading order (how the old site shows stories).
        $this->addMediaCollection('pages');
        $this->addMediaCollection('file')->singleFile();
    }
}
