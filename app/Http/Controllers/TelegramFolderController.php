<?php
namespace App\Http\Controllers;

use App\Models\TelegramFolder;

class TelegramFolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getTelegramFilesAndFolders($chatId, $parentFolderId = null, $page = 1, $messageId = null): array
    {
        $telegramFilesController = new TelegramFilesController();
        $response                = $telegramFilesController->index($chatId, $parentFolderId, $page);

                                          // Extract actual data from response (if it's a JsonResponse)
        $data = $response->getData(true); // true => array

        // Log the extracted data properly

        \Log::info('TelegramFilesController Data:', $data);

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
        return [
            'filesList'       => $filesList,
            'inline_keyboard' => $inline_keyboard,
            'current_page'    => $current_page,
            'last_page'       => $last_page,
        ];
    }
}
