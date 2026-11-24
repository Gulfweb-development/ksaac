<?php

namespace Database\Factories;

use App\Enums\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'site' => Site::Ksaac,
            'title' => ['ar' => 'صفحة '.$title, 'en' => ucfirst($title)],
            'slug' => str($title)->slug(),
            'content' => ['ar' => '<p>محتوى</p>', 'en' => '<p>Content</p>'],
            'is_published' => true,
        ];
    }
}
