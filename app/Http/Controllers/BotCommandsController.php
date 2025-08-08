<?php
namespace App\Http\Controllers;

use App\Helpers\TelegramHelper;
use App\Models\CurrentQueue;
use App\Models\TelegramFiles;
use App\Models\TelegramFolder;
use App\Models\UploadingQueue;
use App\Models\UploadingQueueFiles;
use Illuminate\Support\Facades\Log;

class BotCommandsController extends Controller
{
    public function __construct()
    {
    }
    public function manageFolders($chatId, $parentFolderId = null, $page = 1, $messageId = null): true | false
    {
        $telegramFilesController = new TelegramFilesController();
        $response                = $telegramFilesController->index($chatId, $parentFolderId, $page);
        $data                    = $response->getData(true); // true => array
        $filesList               = "Folders & files in your " . env('APP_NAME') . "\n";
        $name                    = TelegramFolder::where('id', $parentFolderId)->value('name');
        if ($name) {
            $filesList .= "<b>$name</b>\n";
        }
        $lengthOfFolders = count($data['folders']['data']);
        $current_page    = $data['folders']['current_page'];
        $last_page       = $data['folders']['last_page'];
        $inline_keyboard = [];
        if ($lengthOfFolders > 0) {
            foreach ($data['folders']['data'] as $key => $file) {
                $SNo = (($current_page - 1) * 25) + ($key + 1);
                $filesList .= $SNo . ". " . ($file['name'] ?? 'Unnamed') . "\n";
                $inline_keyboard[] = [
                    'text'          => $SNo,
                    'callback_data' => "folder/open/" . $file['id'],
                ];
            }
        }
        $inline_keyboard = array_chunk($inline_keyboard, 5);
        if (count($data['files']['data']) > 0) {
            $filesList .= "\n<b>Files</b>\n\n";
            $inline_keyboard = [];
            foreach ($data['files']['data'] as $key => $file) {
                if ($file['file_name']) {
                    $filesList .= "- " . ($file['file_name'] ?? 'Unnamed') . "\n";
                } else {
                    $filesList .= "- " . ($file['type']) . "\n";
                }

            }
        }
        if ($lengthOfFolders > 0) {
            $filesList .= "\n\n<b>Folders ($current_page/$last_page)</b>";
        } else {
            $filesList .= "\n\nNo Sub Folder found in this folder.";
        }
        return true;
    }
    public function getFolders($chatId, $parentFolderId = null, $page = 1, $messageId = null): true
    {
        $telegramFolderController   = new TelegramFolderController();
        $getTelegramFilesAndFolders = $telegramFolderController->getTelegramFilesAndFolders(
            $chatId, $parentFolderId = null, $page = 1, $messageId = null
        );
        $telegramFilesController = new TelegramFilesController();
        $response                = $telegramFilesController->index($chatId, $parentFolderId, $page);

                                          // Extract actual data from response (if it's a JsonResponse)
        $data = $response->getData(true); // true => array

        // Log the extracted data properly

        Log::info('TelegramFilesController Data:', $data);

        // Convert files to a readable string (example: file names list)
        $filesList = "Please Choose a Folder to upload in " . env('APP_NAME') . "\n";
        $name      = TelegramFolder::where('id', $parentFolderId)->value('name');
        if ($name) {
            $filesList .= "<b>$name</b>\n";
        }

        $lengthOfFolders = count($data['folders']['data']);
        $current_page    = $data['folders']['current_page'];
        $last_page       = $data['folders']['last_page'];
        $inline_keyboard = [];
        if ($lengthOfFolders > 0) {
            foreach ($data['folders']['data'] as $key => $file) {
                $SNo = (($current_page - 1) * 25) + ($key + 1);
                $filesList .= $SNo . ". " . ($file['name'] ?? 'Unnamed') . "\n";
                $inline_keyboard[] = [
                    'text'          => $SNo,
                    'callback_data' => "folder/open/" . $file['id'],
                ];
            }
        }
        $inline_keyboard = array_chunk($inline_keyboard, 5);
        if (count($data['files']['data']) > 0) {
            $filesList .= "\n<b>Files</b>\n\n";
            $inline_keyboard = [];
            foreach ($data['files']['data'] as $key => $file) {
                if ($file['file_name']) {
                    $filesList .= "- " . ($file['file_name'] ?? 'Unnamed') . "\n";
                } else {
                    $filesList .= "- " . ($file['type']) . "\n";
                }

            }
        }
        if ($lengthOfFolders > 0) {
            $filesList .= "\n\n<b>Folders ($current_page/$last_page)</b>";
        } else {
            $filesList .= "\n\nNo Sub Folder found in this folder.";
        }

        $paginationRow = [];
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

        $uploadingQueue = UploadingQueue::create([
            'parent_folder_id' => $folderId,
            'user_id'          => $chatId,
        ]);
        // Create a new queue entry
        CurrentQueue::create([
            'user_id' => $chatId,
            'name'    => "UploadingQueue/$uploadingQueue->id",
        ]);
        $telegramHelper->editMessageText([
            'chat_id'    => $chatId,
            'message_id' => $messageId,
            'text'       => "Ok! Now you can send files to this chat will added.\nAllowed files: photo, video, document, audio.",
        ]);

    }
    public function saveUploadingQueueFiles($props = [])
    {
        $type                = $props['type'] ?? null;
        $file_name           = $props['file_name'] ?? null;
        $mime_type           = $props['mime_type'] ?? null;
        $file_id             = $props['file_id'] ?? null;
        $file_unique_id      = $props['file_unique_id'] ?? null;
        $file_size           = $props['file_size'] ?? null;
        $caption             = $props['caption'] ?? null;
        $uploading_queues_id = $props['uploading_queues_id'] ?? null;
        $chatId              = $props['chat_id'] ?? null;
        UploadingQueueFiles::create([
            'type'                => $type,
            'file_name'           => $file_name,
            'mime_type'           => $mime_type,
            'file_id'             => $file_id,
            'file_unique_id'      => $file_unique_id,
            'file_size'           => $file_size,
            'caption'             => $caption,
            'uploading_queues_id' => $uploading_queues_id,
        ]);
        \Log::info('props:props:chatId', ['chatId' => $chatId]);
        if ($chatId) {
            $file_size      = UploadingQueueFiles::where('uploading_queues_id', $uploading_queues_id)->sum('file_size');
            $telegramHelper = new TelegramHelper();
            return $telegramHelper->sendMessage([
                'chat_id'      => $chatId,
                'text'         => "<b>Added</b>,\n\nSend more or click done button.\nFile Saved Now: " . $this->formatBytes($file_size),
                'parse_mode'   => "HTML",
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            [
                                'text'          => 'Done 👍',
                                'callback_data' => "folder/done/" . $chatId,
                            ],
                        ],
                    ],
                ]),
            ]);
        }

        return true;
    }
    public function doneUploadingQueueFiles($chatId)
    {
        if (! $chatId) {
            return;
        }

        // Get all queues for this user
        $uploadingQueues = UploadingQueue::select(['id', 'parent_folder_id'])
            ->where('user_id', $chatId)
            ->get();

        if ($uploadingQueues->isEmpty()) {
            return; // No queues to process
        }

        foreach ($uploadingQueues as $queue) {
            // Get all files in this queue
            $uploadingQueueFiles = UploadingQueueFiles::where('uploading_queues_id', $queue->id)->get();

            foreach ($uploadingQueueFiles as $file) {
                TelegramFiles::create([
                    'type'             => $file->type,
                    'file_name'        => $file->file_name,
                    'mime_type'        => $file->mime_type,
                    'file_id'          => $file->file_id,
                    'file_unique_id'   => $file->file_unique_id,
                    'file_size'        => $file->file_size,
                    'caption'          => $file->caption,
                    'parent_folder_id' => $queue->parent_folder_id,
                    'user_id'          => $chatId,
                ]);
            }
        }
        // Delete all files for this user (across all queues)
        UploadingQueueFiles::whereIn(
            'uploading_queues_id',
            $uploadingQueues->pluck('id')
        )->delete();

        // Delete all queues for this user
        UploadingQueue::where('user_id', $chatId)->delete();
    }

    public function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        if ($bytes <= 0) {
            return '0 B';
        }

        $pow = floor(log($bytes, 1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
