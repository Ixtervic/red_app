<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $users = User::factory(15)->create();

        $distributions = [
            0, 0, 0,
            1, 1, 1, 1,
            2, 2,
            3, 3,
            4, 4, 4, 4,
        ];

        foreach ($users as $i => $user) {
            $count = $distributions[$i] ?? 0;

            for ($j = 0; $j < $count; $j++) {
                Image::factory()->create([
                    'imageable_id' => $user->id,
                    'imageable_type' => User::class,
                    'created_at' => now()->subDays(30)->addHours($j * 3 + $i * 2),
                    'updated_at' => now()->subDays(30)->addHours($j * 3 + $i * 2),
                ]);
            }
        }
    }
}
