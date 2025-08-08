<?php
namespace App\Http\Controllers;

use App\Models\TelegramFiles;
use App\Models\TelegramFolder;
use Illuminate\Http\Request;

class TelegramFilesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($user_id = null, $parentFolderId = 0, $page = 1, $perPage = 25)
    {
        if ($user_id) {
            $folders = TelegramFolder::where('user_id', $user_id)
                ->where('parent_folder_id', $parentFolderId)
                ->paginate($perPage, ['*'], 'folders_page', $page);

            $files = TelegramFiles::where('user_id', $user_id)
                ->where('parent_folder_id', $parentFolderId)
                ->paginate($perPage, ['*'], 'files_page', $page);

            return response()->json([
                'folders' => $folders,
                'files'   => $files,
            ]);
        }

        return response()->json(['error' => 'User ID is required'], 400);
    }
    public function getFiles($user_id = null, $parentFolderId = 0, $page = 1)
    {
        if ($user_id) {
            $files = TelegramFiles::where('user_id', $user_id)
                ->where('parent_folder_id', $parentFolderId)
                ->paginate(25, ['*'], 'files_page', $page);

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
