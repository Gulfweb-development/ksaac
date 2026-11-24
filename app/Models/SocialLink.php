<?php

namespace App\Models;

use App\Enums\Site;
use App\Enums\SocialPlatform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    use HasFactory;

    protected $fillable = ['site', 'platform', 'url', 'is_active', 'sort'];

    protected function casts(): array
    {
        return [
            'site' => Site::class,
            'platform' => SocialPlatform::class,
            'is_active' => 'boolean',
        ];
    }
}
