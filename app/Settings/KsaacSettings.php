<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/** KSAAC website identity, contact details and footer. Bilingual values are ['ar' => ..., 'en' => ...]. */
class KsaacSettings extends Settings
{
    public array $org_name;

    public array $tagline;

    public ?string $logo;

    public ?string $phone;

    public ?string $email;

    public array $address;

    public ?string $ios_app_url;

    public ?string $android_app_url;

    public array $footer_about;

    public array $copyright;

    public static function group(): string
    {
        return 'ksaac';
    }
}
