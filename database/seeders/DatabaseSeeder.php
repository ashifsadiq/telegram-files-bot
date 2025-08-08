<?php
namespace Database\Seeders;

use App\Models\TelegramUsers;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        TelegramUsers::create([
            'user_id'=>env('DEVELOPER_TG_ID'),
            'first_name'=>'Ashif'
        ]);
        $this->call([
            BotCommandsSeeder::class,
            TelegramFolderSeeder::class,
        ]);
        User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
