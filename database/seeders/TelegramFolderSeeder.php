<?php
namespace Database\Seeders;

use App\Models\TelegramFiles;
use App\Models\TelegramFolder;
use Illuminate\Database\Seeder;

class TelegramFolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < fake()->numberBetween(500, 1999); $i++) {
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
        for ($i = 0; $i < fake()->numberBetween(500, 1000); $i++) {
            TelegramFiles::create([
                "type"             => "photo",
                "file_name"        => null,
                "mime_type"        => null,
                "file_id"          => "AgACAgUAAxkBAAJVwWiWJjFDneEpvFmGCLj3GBJAfTWpAAJjyDEblIOwVMb0jkQbubXsAQADAgADeAADNgQ",
                "file_unique_id"   => "AQADY8gxG5SDsFR9",
                "file_size"        => "82730",
                "caption"          => fake()->paragraph(),
                "parent_folder_id" => null,
                "user_id"          => "824045233",
            ]);
        }
    }
}
