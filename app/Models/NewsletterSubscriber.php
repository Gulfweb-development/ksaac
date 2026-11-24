<?php

namespace App\Models;

use App\Enums\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    use HasFactory;

    protected $fillable = ['site', 'email', 'locale', 'unsubscribed_at'];

    protected function casts(): array
    {
        return [
            'site' => Site::class,
            'unsubscribed_at' => 'datetime',
        ];
    }

    public function scopeSubscribed(Builder $query): void
    {
        $query->whereNull('unsubscribed_at');
    }
}
