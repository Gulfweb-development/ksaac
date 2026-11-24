<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    use HasFactory, HasTranslatableFields;

    public array $translatable = ['label'];

    protected $fillable = ['value', 'suffix', 'label', 'animate', 'is_active', 'sort'];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'animate' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
