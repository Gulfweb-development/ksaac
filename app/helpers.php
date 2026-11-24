<?php

use App\Settings\ContactSettings;
use App\Settings\GeneralSettings;
use App\Settings\HomepageSettings;
use App\Settings\KsaacSettings;

/** setting('group.key') or a nested path such as setting('ksaac.org_name.ar'). */
function setting($key, $default = null)
{
    $groups = [
        'general' => GeneralSettings::class,
        'contact' => ContactSettings::class,
        'ksaac' => KsaacSettings::class,
        'homepage' => HomepageSettings::class,
    ];

    try {
        [$group, $path] = array_pad(explode('.', $key, 2), 2, null);

        if (! isset($groups[$group]) || $path === null) {
            return $default;
        }

        return data_get(app($groups[$group])->toArray(), $path) ?? $default;
    } catch (\Throwable $th) {
        return $default;
    }
}
