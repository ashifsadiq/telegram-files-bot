<?php
namespace App\Helpers;

use App\Http\Controllers\BotCommandsController;
use App\Models\BotCommands;
use Illuminate\Http\Request;

class BotCommandHelper
{
    public function __construct()
    {
    }
    public function handleBotCommands(Request $request)
    {
        $message = $request->input('message');
        $chatId  = $message['chat']['id'] ?? null;
        $text    = $message['text'] ?? null;

        if ($text && $chatId) {
            if (strpos($text, '/') === 0) {
                $parts     = explode(' ', $text, 2);                 // limit to 2 parts
                $command   = explode('@', ltrim($parts[0], '/'))[0]; // handle /command@bot
                $arguments = $parts[1] ?? '';

                $botCommand = BotCommands::where('command', $command)->first();
                if (! $botCommand) {
                    $replyText   = "🤔 Unrecognized command: /$command 😂, \n\n";
                    $allCommands = BotCommands::all();
                    if ($allCommands->count() > 0) {
                        $replyText .= "Available:\n";
                        foreach ($allCommands as $command) {
                            $replyText .= "/{$command->command} - {$command->description}\n";
                        }
                        $telegramHelper = new TelegramHelper();
                        return $telegramHelper->sendMessage([
                            'chat_id'    => $chatId,
                            'text'       => $replyText,
                            'parse_mode' => 'HTML',
                        ]);
                    }
                    // abort(200);
                }
                if ($botCommand->command === "addfiles") {
                    $telegramHelper = new TelegramHelper();
                    $telegramHelper->checkSubscription($request);
                    $botCommandsController = new BotCommandsController();
                    return $botCommandsController->getFolders($chatId);
                }
                if ($botCommand->command === 'start') {
                    $telegramHelper = new TelegramHelper();
                    return $telegramHelper->sendMessage([
                        'chat_id' => $chatId,
                        'text'    => $botCommand->reply,
                    ]);
                }
                if ($botCommand->command === 'managefolder') {
                    $telegramHelper = new TelegramHelper();
                    $telegramHelper->checkSubscription($request);
                    return $telegramHelper->sendMessage([
                        'chat_id' => $chatId,
                        'text'    => $botCommand->reply,
                    ]);
                }
                // Optional fallback for unknown commands
            }
        }
    }
}
