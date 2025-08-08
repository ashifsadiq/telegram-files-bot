<?php
namespace Database\Seeders;

use App\Models\BotCommands;
use Illuminate\Database\Seeder;

class BotCommandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BotCommands::create([
            'command'     => 'start',
            'description' => '✅ Start the bot',
            'reply_type'  => 'text',
            'reply'       => 'Bot is working fine',
        ]);
        BotCommands::create([
            'command'     => 'addfiles',
            'description' => '📁 Add files',
            'reply_type'  => 'text',
            'reply'       => 'Add files to this bot',
        ]);
    }
}
