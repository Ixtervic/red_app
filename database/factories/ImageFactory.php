<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'url' => 'https://picsum.photos/id/' . fake()->numberBetween(1, 300) . '/400/400',
            'imageable_id' => User::factory(),
            'imageable_type' => User::class,
        ];
    }
}
