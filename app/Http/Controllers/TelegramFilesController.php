<?php
namespace App\Http\Controllers;

use App\Helpers\TelegramHelper;
use App\Models\TelegramFiles;
use App\Models\TelegramFolder;
use Illuminate\Http\Request;

class TelegramFilesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($user_id = null, $parentFolderId = 0, $page = 1, $perPage = 25, $getFolder = true, $getFiles = true)
    {
        if ($user_id) {
            $folders = [];
            $files   = [];
            if ($getFolder) {
                $folders = TelegramFolder::where('user_id', $user_id)
                    ->where('parent_folder_id', $parentFolderId)
                    ->paginate($perPage, ['*'], 'folders_page', $page);
            }
            if ($getFiles) {
                $files = TelegramFiles::where('user_id', $user_id)
                    ->where('parent_folder_id', $parentFolderId)
                    ->paginate($perPage, ['*'], null, $page);
            }

            return response()->json([
                'folders' => $folders,
                'files'   => $files,
            ]);
        }

        return response()->json(['error' => 'User ID is required'], 400);
    }
    public function getFiles($user_id = null, $parentFolderId = 0, $page = 1, $perPage = 25)
    {
        $telegramHelper = new TelegramHelper();
        $telegramHelper->sendMessage([
            'chat_id' => $user_id,
            'text'    => "user_id: $user_id\nparentFolderId: $parentFolderId\npage: $page\nperPage: $perPage",
        ]);
        if ($user_id) {
            $files = TelegramFiles::where('user_id', $user_id)
                ->where('parent_folder_id', $parentFolderId)
                ->paginate($perPage, ['*'], null, $page);

            return $files;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TelegramFiles $telegramFiles)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TelegramFiles $telegramFiles)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TelegramFiles $telegramFiles)
    {
        //
    }
}
