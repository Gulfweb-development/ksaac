<?php

namespace App\Models;

use App\Enums\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = ['site', 'name', 'email', 'phone', 'subject', 'message', 'read_at'];

    protected function casts(): array
    {
        return [
            'site' => Site::class,
            'read_at' => 'datetime',
        ];
    }

    public function scopeUnread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    public function markAsRead(): void
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }
}
