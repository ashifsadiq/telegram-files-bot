<?php
namespace App\Http\Controllers;

use App\Helpers\TelegramHelper;
use App\Models\CurrentQueue;
use App\Models\TelegramFiles;
use App\Models\UploadingQueue;
use App\Models\UploadingQueueFiles;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BotCommandsController extends Controller
{
    public function __construct()
    {
    }
    public function manageFolders($chatId, $parentFolderId = null, $page = 1, $messageId = null): true
    {
        $telegramFolderController   = new TelegramFolderController();
        $getTelegramFilesAndFolders = $telegramFolderController->getTelegramFilesAndFolders(
            $chatId,
            $parentFolderId,
            $page,
            'manageFolders/open/'
        );
        $filesList       = $getTelegramFilesAndFolders['filesList'];
        $inline_keyboard = $getTelegramFilesAndFolders['inline_keyboard'];
        $current_page    = $getTelegramFilesAndFolders['current_page'];
        $last_page       = $getTelegramFilesAndFolders['last_page'];
        $files_count     = $getTelegramFilesAndFolders['files_count'];
        $paginationRow   = [];
        if ($current_page > 1) {
            $paginationRow[] = [
                'text'          => '⬅️ Previous',
                'callback_data' => "manageFolders/page/" . $current_page - 1,
            ];
        }
        if ($parentFolderId) {
            $paginationRow[] = [
                'text'          => 'Back ⬆',
                'callback_data' => "manageFolders/back/" . $parentFolderId,
            ];
        }
        if ($parentFolderId) {
            $paginationRow[] = [
                'text'          => '✏️ Folder',
                'callback_data' => "manageFolders/edit/" . $parentFolderId,
            ];
        }
        if ($files_count) {
            $paginationRow[] = [
                'text'          => '👀 Files',
                'callback_data' => "manageFolders/view/" . $parentFolderId,
            ];
        }

        if ($current_page < $last_page) {
            $paginationRow[] = [
                'text'          => 'Next ➡️',
                'callback_data' => "manageFolders/page/" . $current_page + 1,
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
        return true;
    }
    public function getFolders($chatId, $parentFolderId = null, $page = 1, $messageId = null): true
    {
        $telegramFolderController   = new TelegramFolderController();
        $getTelegramFilesAndFolders = $telegramFolderController->getTelegramFilesAndFolders(
            $chatId,
            $parentFolderId,
            $page,
            'folder/open/'
        );
        $filesList       = $getTelegramFilesAndFolders['filesList'];
        $inline_keyboard = $getTelegramFilesAndFolders['inline_keyboard'];
        $current_page    = $getTelegramFilesAndFolders['current_page'];
        $last_page       = $getTelegramFilesAndFolders['last_page'];
        $paginationRow   = [];
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

    public function getFiles($chatId, $parentFolderId, $page = 1, $messageId = null): true
    {
        $getFiles = new TelegramFilesController()->getFiles(
            $chatId,
            $parentFolderId,
            $page
        );
        foreach ($getFiles as $key => $file) {
            if (isset($file['type']) && $file['type'] === 'photo') {
                new TelegramHelper()->sendPhoto([
                    'chat_id'      => $chatId,
                    'photo'        => $file['file_id'], // Must be a file_id, URL, or InputFile (resource)
                    'caption'      => $file['caption'],
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [
                            [
                                [
                                    'text'          => '🚮 Delete',
                                    'callback_data' => "file/delete/" . $file['id'] . "-" . $chatId,
                                ],
                            ],
                        ],
                    ]),
                ]);
            }
        }
        Storage::disk('local')->put(
            'TelegramFilesController_getFiles.json',
            json_encode($getFiles ?? [], JSON_PRETTY_PRINT)
        );
        $telegramHelper = new TelegramHelper();
        $telegramHelper->sendMessage([
            'chat_id'    => $chatId,
            'text'       => 'file saved: TelegramFilesController_getFiles',
            'parse_mode' => 'HTML',
        ]);
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
