<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/** Journal of Arab Children website: identity, contact, app links, footer and the bank accounts subscribers pay into. */
class JacSettings extends Settings
{
    public array $name;

    public ?string $logo;

    public ?string $favicon;

    public ?string $phone;

    public ?string $fax;

    public ?string $email;

    public array $address;

    public array $po_box;

    public ?string $ios_app_url;

    public ?string $android_app_url;

    public array $footer_about;

    public array $copyright;

    public array $bank_name;

    public array $account_holder;

    // Each account: currency, account_number, swift, iban.
    public array $bank_accounts;

    public static function group(): string
    {
        return 'jac';
    }
}
