<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '7' . fake()->randomElement(['71', '72']) . fake()->numberBetween(10000000, 9999999),
        ];
    }
}
