<?php

namespace Database\Factories;

use App\Enums\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsletterSubscriberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'site' => fake()->randomElement(Site::cases()),
            'email' => fake()->unique()->safeEmail(),
            'locale' => fake()->randomElement(['ar', 'en']),
        ];
    }
}
