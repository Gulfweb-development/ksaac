<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SocialPlatform: string implements HasLabel
{
    case X = 'x';
    case Facebook = 'facebook';
    case Instagram = 'instagram';
    case LinkedIn = 'linkedin';
    case YouTube = 'youtube';
    case Pinterest = 'pinterest';
    case WhatsApp = 'whatsapp';
    case Telegram = 'telegram';
    case TikTok = 'tiktok';

    public function getLabel(): string
    {
        return match ($this) {
            self::X => 'X',
            self::LinkedIn => 'LinkedIn',
            self::YouTube => 'YouTube',
            self::WhatsApp => 'WhatsApp',
            self::TikTok => 'TikTok',
            default => $this->name,
        };
    }
}
