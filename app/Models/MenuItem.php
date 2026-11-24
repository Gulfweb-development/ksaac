<?php

namespace App\Models;

use App\Enums\Site;
use App\Models\Concerns\HasTranslatableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory, HasTranslatableFields;

    public array $translatable = ['label', 'badge'];

    protected $fillable = ['site', 'parent_id', 'label', 'url', 'badge', 'open_in_new_tab', 'is_active', 'sort'];

    protected function casts(): array
    {
        return [
            'site' => Site::class,
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    /** 1 = top item, 2 = mega-menu column, 3 = link inside a column. */
    public function depth(): int
    {
        return $this->parent_id ? $this->parent->depth() + 1 : 1;
    }
}
