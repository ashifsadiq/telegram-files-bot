<?php
namespace App\Http\Controllers;

use App\Models\TelegramFolder;

class TelegramFolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getTelegramFilesAndFolders($chatId, $parentFolderId = null, $page = 1, $basePath = ''): array
    {
        $telegramFilesController = new TelegramFilesController();
        $perPage                 = 10;
        $response                = $telegramFilesController->index(
            $chatId,
            $parentFolderId,
            $page,
            $perPage,
            true,
        );

                                          // Extract actual data from response (if it's a JsonResponse)
        $data = $response->getData(true); // true => array
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
        $filesList .= "Page: <b>$current_page/$last_page</b>\n";
        $currentPath = [];
        $currentId   = $parentFolderId;

        if ($currentId) {
            while ($currentId != null) {
                $folder = TelegramFolder::select(['id', 'name', 'parent_folder_id'])
                    ->where('id', $currentId)
                    ->first();

                if ($folder) {
                    $currentPath[] = $folder->name . "/";
                    $currentId     = $folder->parent_folder_id; // move up to parent folder
                } else {
                    $currentId = null;
                }
            }
        }

        $currentPath       = array_reverse($currentPath); // Root → Child order
        $currentPathString = implode(' 👉 ', $currentPath);
        if ($currentPathString) {
            $filesList .= "Path: $currentPathString\n";
        }

        if ($lengthOfFolders > 0) {
            foreach ($data['folders']['data'] as $key => $file) {
                $SNo               = (($current_page - 1) * $perPage) + ($key + 1);
                $inline_keyboard[] = [
                    'text'          => ($file['name'] ?? 'Unnamed') . " 📁",
                    'callback_data' => $basePath . $file['id'],
                ];
            }
        }
        $inline_keyboard = array_chunk($inline_keyboard, 1);
        return [
            'filesList'       => $filesList,
            'inline_keyboard' => $inline_keyboard,
            'current_page'    => $current_page,
            'last_page'       => $last_page,
            'folders_count'   => count($data['folders']['data']),
            'files_count'     => count($data['files']['data']),
            'files_total'     => $data['filesCount'],
        ];
    }
    public function renameFolder($userID, $parentFolderId, $folderName)
    {
        try {
            if (! is_null($parentFolderId) & ! is_null($folderName)) {
                TelegramFolder::where([
                    'user_id' => $userID,
                    'id'      => $parentFolderId,
                ])->update([
                    'name' => $folderName,
                ]);
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
    public function deleteFolder($userID, $parentFolderId)
    {
        try {
            if (! is_null($parentFolderId)) {
                TelegramFolder::where([
                    'user_id' => $userID,
                    'id'      => $parentFolderId,
                ])->delete();
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            return $th->getMessage();
        }

    }
}
