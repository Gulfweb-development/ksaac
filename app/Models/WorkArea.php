<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkArea extends Model
{
    use HasFactory, HasTranslatableFields;

    public array $translatable = ['title', 'description'];

    protected $fillable = ['title', 'description', 'is_active', 'sort'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
