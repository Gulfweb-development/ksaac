<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublicationCategory extends Model
{
    use HasFactory, HasTranslatableFields;

    public array $translatable = ['name'];

    protected $fillable = ['name', 'slug', 'sort'];

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }
}
