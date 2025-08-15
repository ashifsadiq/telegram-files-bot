<?php
namespace App\Http\Controllers;

use App\Helpers\TelegramHelper;
use App\Models\TelegramUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

class TelegramWebController extends Controller
{
    public function __construct()
    {
    }
    public function bot(Request $request): true | \Illuminate\Http\JsonResponse
    {
        // \Log::info('Incoming telegram update:', $request->all());
        try {
            $message = $request->input('message') ?? $request->input('edited_message');
            $chatId  = $message['chat']['id'] ?? null;
            $user    = TelegramUsers::firstOrCreate([
                'user_id' => $chatId,
            ], [
                'user_id'    => $chatId,
                'first_name' => $message['chat']['first_name'],
                'last_name'  => $message['chat']['last_name'],
                'username'   => $message['chat']['username'],
            ]);
            $user->update([
                'used' => now(),
            ]);
            $user->save();
            $telegramHelper = new TelegramHelper();
            if (isset($message['entities'][0]['type']) && ($message['entities'][0]['type'] == 'bot_command')) {
                $telegramHelper->handleBotCommands($request);
                return true;
            }

            $telegramHelper->handleCallbackQuery($request);
            if (! $message) {
                \Log::warning('No message or edited_message found in update.');
                return response()->json(['status' => 'ignored'], 200);
            }

            $typeMap = [
                'text'     => 'manageTextSend',
                'photo'    => 'managePhotoSend',
                'video'    => 'manageVideoSend',
                'document' => 'manageDocumentSend',
                'audio'    => 'manageAudioSend',
                'voice'    => 'manageVoiceSend',
                'sticker'  => 'manageStickerSend',
                'location' => 'manageLocationSend',
            ];

            $type = 'unknown';
            foreach ($typeMap as $key => $method) {
                if (isset($message[$key])) {
                    $type = $key;
                    $telegramHelper->$method($request);
                    break;
                }
            }
            if ($type === 'unknown') {
                $telegramHelper->manageUnknownSend($request);
            }
        } catch (\Throwable $th) {
            $telegramHelper->sendMessage([
                'chat_id'    => env('DEVELOPER_TG_ID'),
                'text'       => 'Telegram bot error:' . $th->getMessage(),
                'parse_mode' => 'HTML',
            ]);
            \Log::error('Telegram bot error:', ['exception' => $th]);
        }
        return response()->json(['status' => 'ok']);
    }

    public function index()
    {
        $telegramHelper    = new TelegramHelper();
        $currentWebHookUrl = $telegramHelper->webhookInfo()['url'] ?? 'Currently no webhook is set up';
        return Inertia::render('Settings/TelegramWebhook', [
            'currentWebHookUrl' => $currentWebHookUrl,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $telegramHelper = new TelegramHelper();
        $request->validate([
            'base_url' => 'required|url:https',
        ]);
        $telegramHelper->setWebhook([
            'url' => $request->base_url . 'api/telegram/webhooks/inbound',
        ]);
        return [
            'success' => true,
            'message' => "Webhook updated successfully" . route('api.webhook.inbound'),
        ];
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
    public function reset()
    {
        try {
            $telegramHelper = new TelegramHelper();
            $homeUrl        = request()->getSchemeAndHttpHost() . '/';
            $telegramHelper->setWebhook([
                'url' => $homeUrl . 'api/telegram/webhooks/inbound',
            ]);
            return [
                'success' => true,
                'message' => "Webhook reset successfully" . route('api.webhook.inbound'),
            ];
        } catch (\Throwable $th) {
            return [
                'success' => true,
                'message' => $homeUrl . " - " . $th->getMessage(),
            ];
        }
    }
    public function destroy()
    {
        $telegramHelper = new TelegramHelper();
        $telegramHelper->deleteWebhook();
        return [
            'success' => true,
            'message' => "Webhook deleted successfully",
        ];
    }

    public function migrateFreshSeed(Request $request)
    {
        // Correct way: pass command name and options separately
        Artisan::call('migrate:fresh', [
            '--seed' => true,
        ]);

        // Capture output
        $output = Artisan::output();
        return back();
    }

}
