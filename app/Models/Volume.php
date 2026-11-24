<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Volume extends Model
{
    use HasFactory, HasTranslatableFields;

    public array $translatable = ['title'];

    protected $fillable = ['number', 'title', 'year'];

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'year' => 'integer',
        ];
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class)->orderByDesc('number');
    }
}
