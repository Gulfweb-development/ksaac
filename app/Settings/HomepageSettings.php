<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * KSAAC homepage: section order/visibility/headings, plus the About and Journal blocks.
 * Bilingual values are ['ar' => ..., 'en' => ...]; both homepage designs read these.
 */
class HomepageSettings extends Settings
{
    // Each item: key, visible, kicker, title, subtitle (bilingual).
    public array $sections;

    public array $about_body;

    public array $about_quote;

    public array $about_quote_by;

    public array $about_button_label;

    public ?string $about_button_url;

    // Up to two photo paths shown beside the About text.
    public array $about_images;

    public array $journal_body;

    public array $journal_button_label;

    public ?string $journal_url;

    public ?string $journal_image;

    // Each tag: ar, en.
    public array $journal_tags;

    public static function group(): string
    {
        return 'homepage';
    }

    /** Section keys the front end knows how to render, in default order. */
    public const SECTIONS = ['hero', 'stats', 'about', 'news', 'publications', 'journal', 'conferences', 'project', 'kids', 'partners', 'newsletter'];
}
