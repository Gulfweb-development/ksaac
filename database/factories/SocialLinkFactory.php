<?php

namespace Database\Factories;

use App\Enums\Site;
use App\Enums\SocialPlatform;
use Illuminate\Database\Eloquent\Factories\Factory;

class SocialLinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'site' => fake()->randomElement(Site::cases()),
            'platform' => fake()->randomElement(SocialPlatform::cases()),
            'url' => fake()->url(),
            'is_active' => true,
            'sort' => 0,
        ];
    }
}
