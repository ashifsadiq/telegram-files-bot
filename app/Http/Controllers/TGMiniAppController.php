<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class TGMiniAppController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function files(Request $request, $chatID)
    {
        $parentFolderId = $request->input('parentFolderId');
        if (! $parentFolderId) {
            return inertia('MiniApp/Files', [
                'chatID' => $chatID,
                'files'  => [],
            ]);
        }
        $page                    = $request->input('page') ?? 1;
        $telegramFilesController = new TelegramFilesController();
        $files                   = $telegramFilesController->index(
            $chatID,
            $parentFolderId,
            $page,
            null,
            false,
            true,
        );
        $data = $files->getData(true);
        return inertia('MiniApp/Files', [
            'chatID'         => $chatID,
            'files'          => $data['files'],
            'parentFolderId' => $parentFolderId,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
