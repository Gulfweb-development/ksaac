<?php

namespace App\Models;

use App\Enums\Site;
use App\Models\Concerns\HasTranslatableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class HeroSlide extends Model implements HasMedia
{
    use HasFactory, HasTranslatableFields, InteractsWithMedia;

    public array $translatable = ['kicker', 'title', 'subtitle', 'button_label'];

    protected $fillable = ['site', 'kicker', 'title', 'subtitle', 'button_label', 'button_url', 'is_active', 'sort'];

    protected function casts(): array
    {
        return [
            'site' => Site::class,
            'is_active' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
        $this->addMediaCollection('video')->singleFile();
    }
}
