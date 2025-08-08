<?php
namespace Database\Seeders;

use App\Models\TelegramFolder;
use Illuminate\Database\Seeder;

class TelegramFolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < fake()->numberBetween(500, 1000); $i++) {
            TelegramFolder::create([
                'name'             => fake()->name(),
                'sharable'         => fake()->boolean(20),
                'parent_folder_id' => fake()->randomElement([
                    null,
                    optional(TelegramFolder::inRandomOrder()->first())->id,
                ]),
                'user_id'          => env('DEVELOPER_TG_ID'),
            ]);
        }
    }
}
