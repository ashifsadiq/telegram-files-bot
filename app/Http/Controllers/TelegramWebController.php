<?php
namespace App\Http\Controllers;

use App\Helpers\TelegramHelper;
use Illuminate\Http\Request;
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
            $message        = $request->input('message') ?? $request->input('edited_message');
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
                'chat_id'    => '824045233',
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
    public function destroy()
    {
        $telegramHelper = new TelegramHelper();
        $telegramHelper->deleteWebhook();
        return [
            'success' => true,
            'message' => "Webhook deleted successfully",
        ];
    }
}
