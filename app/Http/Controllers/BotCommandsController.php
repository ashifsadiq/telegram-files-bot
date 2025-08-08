<?php
namespace App\Http\Controllers;

use App\Helpers\TelegramHelper;
use App\Http\Requests\StoreBotCommandsRequest;
use App\Http\Requests\UpdateBotCommandsRequest;
use App\Models\BotCommands;
use App\Models\CurrentQueue;
use App\Models\TelegramFolder;
use Illuminate\Support\Facades\Log;

class BotCommandsController extends Controller
{
    public function __construct()
    {
    }
    public function getFolders($chatId, $parentFolderId = null, $page = 1, $messageId = null): true
    {
        $telegramFilesController = new TelegramFilesController();
        $response                = $telegramFilesController->index($chatId, $parentFolderId, $page);

                                          // Extract actual data from response (if it's a JsonResponse)
        $data = $response->getData(true); // true => array

        // Log the extracted data properly

        Log::info('TelegramFilesController Data:', $data);

        // Convert files to a readable string (example: file names list)
        $filesList = "Please Choose a Folder to upload\n";
        $name      = TelegramFolder::where('id', $parentFolderId)->value('name');
        if ($name) {
            $filesList .= "<b>$name</b>\n";
        }

        $lengthOfFolders = count($data['folders']['data']);
        $current_page    = $data['folders']['current_page'];
        $last_page       = $data['folders']['last_page'];
        $inline_keyboard = [];
        if ($lengthOfFolders > 0) {
            foreach ($data['folders']['data'] as $key => $folder) {
                $SNo = (($current_page - 1) * 25) + ($key + 1);
                $filesList .= $SNo . ". " . ($folder['name'] ?? 'Unnamed') . "\n";
                $inline_keyboard[] = [
                    'text'          => $SNo,
                    'callback_data' => "folder/open/" . $folder['id'],
                ];
            }
        }
        $inline_keyboard = array_chunk($inline_keyboard, 5);
        if (count($data['files']['data']) > 0) {
            $filesList .= "\n<b>Files</b>\n\n";
            $inline_keyboard = [];
            foreach ($data['files']['data'] as $key => $folder) {
                $filesList .= "- " . ($folder['file_name'] ?? 'Unnamed') . "\n";
                $inline_keyboard[] = [
                    'text' => "$key",
                    // 'callback_data' => "folder/node/$folder->id",
                ];
            }
        }
        $paginationRow = [];
        if ($lengthOfFolders > 0) {
            $filesList .= "\n\n<b>Folders ($current_page/$last_page)</b>";
        } else {
            $filesList .= "\n\nNo Sub Folder found in this folder.";
        }

        if ($current_page > 1) {
            $paginationRow[] = [
                'text'          => '⬅️ Previous',
                'callback_data' => "folder/page/" . $current_page - 1,
            ];
        }
        if ($parentFolderId) {
            $paginationRow[] = [
                'text'          => 'Back ⬆',
                'callback_data' => "folder/back/" . $parentFolderId,
            ];
        }
        $paginationRow[] = [
            'text'          => 'Add Here ➕',
            'callback_data' => "folder/add/" . $parentFolderId,
        ];
        if ($current_page < $last_page) {
            $paginationRow[] = [
                'text'          => 'Next ➡️',
                'callback_data' => "folder/page/" . $current_page + 1,
            ];
        }

        if (! empty($paginationRow)) {
            $inline_keyboard[] = $paginationRow;
        }
        $telegramHelper = new TelegramHelper();
        if ($messageId) {
            $telegramHelper->editMessageText([
                'chat_id'      => $chatId,
                'message_id'   => $messageId,
                'text'         => $filesList ?: 'No files found.',
                'parse_mode'   => 'HTML',
                'reply_markup' => json_encode([
                    'inline_keyboard' => $inline_keyboard,
                ]),
            ]);
        } else {
            $telegramHelper->sendMessage([
                'chat_id'      => $chatId,
                'text'         => $filesList ?: 'No files found.',
                'parse_mode'   => 'HTML',
                'reply_markup' => json_encode([
                    'inline_keyboard' => $inline_keyboard,
                ]),
            ]);
        }

        // abort(200);
        return true;
    }
    public function filesAddCurrentQueue($chatId, $messageId, $folderId): void
    {
        $telegramHelper = new TelegramHelper();

        // Delete previous queue entries
        CurrentQueue::where(['user_id' => $chatId])->delete();

        // Create a new queue entry
        CurrentQueue::create([
            'user_id' => $chatId,
            'name'    => "BotCommandsController/filesAdd/$folderId",
        ]);
        $telegramHelper->editMessageText([
            'chat_id'      => $chatId,
            'message_id'   => $messageId,
            'text'    => "Ok! Now you can send files to this chat will added.\nAllowed files: photo, video, document, audio.",
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBotCommandsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(BotCommands $botCommands)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BotCommands $botCommands)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBotCommandsRequest $request, BotCommands $botCommands)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BotCommands $botCommands)
    {
        //
    }
}
