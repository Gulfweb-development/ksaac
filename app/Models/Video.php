<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory, HasTranslatableFields;

    public array $translatable = ['title'];

    protected $fillable = ['title', 'youtube_url', 'is_active', 'sort'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** YouTube video id from watch, youtu.be, embed or shorts links. */
    public function youtubeId(): ?string
    {
        preg_match('~(?:youtu\.be/|v=|embed/|shorts/)([A-Za-z0-9_-]{11})~', (string) $this->youtube_url, $m);

        return $m[1] ?? null;
    }

    public function thumbnailUrl(): ?string
    {
        return ($id = $this->youtubeId()) ? "https://i.ytimg.com/vi/$id/hqdefault.jpg" : null;
    }
}
