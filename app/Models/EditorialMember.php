<?php

namespace App\Models;

use App\Enums\EditorialGroup;
use App\Models\Concerns\HasTranslatableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class EditorialMember extends Model implements HasMedia
{
    use HasFactory, HasTranslatableFields, InteractsWithMedia;

    public array $translatable = ['name', 'role'];

    protected $fillable = ['group', 'name', 'role', 'is_deceased', 'deceased_on', 'is_active', 'sort'];

    protected function casts(): array
    {
        return [
            'group' => EditorialGroup::class,
            'is_deceased' => 'boolean',
            'deceased_on' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
    }
}
