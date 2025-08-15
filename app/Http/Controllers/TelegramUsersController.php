<?php
namespace App\Http\Controllers;

use App\Helpers\TelegramHelper;
use App\Http\Requests\StoreTelegramUsersRequest;
use App\Http\Requests\UpdateTelegramUsersRequest;
use App\Models\TelegramUsers;
use function GuzzleHttp\json_encode;
use Illuminate\Http\Request;

class TelegramUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function firstOrCreateTelegramUsers(Request $request)
    {
        try {
            $message = $request->input('message') ?? $request->input('edited_message') ?? $request->input('callback_query');
            if (! $message) {
                $telegramHelper = new TelegramHelper();
                $telegramHelper->sendMessage([
                    'chat_id' => env('DEVELOPER_TG_ID'),
                    'text'    => "Cannot get message from",
                ]);
                $telegramHelper->sendMessage([
                    'chat_id' => env('DEVELOPER_TG_ID'),
                    'text'    => json_encode($request->all()),
                ]);
                return;
            }
            $chatId    = $message['chat']['id'] ?? null;
            $firstName = $message['chat']['first_name'] ?? 'NA';
            $lastname  = $message['chat']['last_name'] ?? null;
            $username  = $message['chat']['username'] ?? null;

            if ($chatId) {
                \Log::info('', $message);
                $user = TelegramUsers::firstOrCreate([
                    'user_id' => $chatId,
                ], [
                    'user_id'    => $chatId,
                    'first_name' => $firstName,
                    'last_name'  => $lastname,
                    'username'   => $username,
                ]);
                $user->update([
                    'used' => now(),
                ]);
                $user->save();
            } else {
                $telegramHelper = new TelegramHelper();
                $telegramHelper->sendMessage([
                    'chat_id' => env('DEVELOPER_TG_ID'),
                    'text'    => "Cannot get Chat from",
                ]);
                $telegramHelper->sendMessage([
                    'chat_id' => env('DEVELOPER_TG_ID'),
                    'text'    => json_encode($request->all()),
                ]);
            }
        } catch (\Throwable $th) {
            $telegramHelper = new TelegramHelper();
            $telegramHelper->sendMessage([
                'chat_id'    => env('DEVELOPER_TG_ID'),
                'text'       => 'Telegram bot error:' . $th->getMessage() . "\nFile: " . $th->getFile() . "\nLine: " . $th->getLine(),
                'parse_mode' => 'HTML',
            ]);
            \Log::error('Error:TelegramUsersController.php#L27:', ['exception' => $th]);
        }
        //
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
    public function store(StoreTelegramUsersRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TelegramUsers $telegramUsers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TelegramUsers $telegramUsers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTelegramUsersRequest $request, TelegramUsers $telegramUsers)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TelegramUsers $telegramUsers)
    {
        //
    }
}
