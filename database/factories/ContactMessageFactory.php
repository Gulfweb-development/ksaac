<?php

namespace Database\Factories;

use App\Enums\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'site' => fake()->randomElement(Site::cases()),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('9#######'),
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraph(),
        ];
    }
}
