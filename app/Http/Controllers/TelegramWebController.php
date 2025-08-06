<?php
namespace App\Http\Controllers;

use App\Helpers\TelegramHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Telegram\Bot\Api;

class TelegramWebController extends Controller
{
    protected $telegramHelper;
    public function __construct()
    {
        $this->telegramHelper = new TelegramHelper();
    }
    public function bot(Request $request)
    {
        \Log::info('Incoming telegram update:', $request->all());

        $message = $request->input('message');

        if (isset($message['text'])) {
            $type = 'text';
        } elseif (isset($message['photo'])) {
            $type = 'photo';
        } elseif (isset($message['video'])) {
            $type = 'video';
        } elseif (isset($message['document'])) {
            $type = 'document';
        } elseif (isset($message['audio'])) {
            $type = 'audio';
        } elseif (isset($message['voice'])) {
            $type = 'voice';
        } elseif (isset($message['sticker'])) {
            $type = 'sticker';
        } elseif (isset($message['location'])) {
            $type = 'location';
        } else {
            $type = 'unknown';
        }
        // Optional: respond accordingly
        $chatId = $message['chat']['id'] ?? null;
        $text   = match ($type) {
            'photo' => 'You send a photo!',
            'video' => 'You send a video!',
            'document' => 'You send a document!',
            'audio' => 'You send a audio!',
            'voice' => 'You send a voice!',
            'sticker' => 'You send a sticker!',
            'location' => 'You send a location!',
            'text' => "You sent a text: " . $message['text'],
            default => "Unrecognized message type: $type",
        };

        if ($chatId) {
            $reply_markup = $this->telegramHelper->replyKeyboardMarkup();
            $this->telegramHelper->sendMessage([
                'chat_id'      => $chatId,
                'text'         => $text,
                'reply_markup' => $reply_markup,
            ]);
        }
    }
    public function index()
    {
        $currentWebHookUrl = $this->telegramHelper->webhookInfo()['url'] ?? 'Currently no webhook is set up';
        return Inertia::render('Settings/TelegramWebhook', [
            'currentWebHookUrl' => $currentWebHookUrl,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $telegram = new Api(env('BOT_TOKEN'));
        $request->validate([
            'base_url' => 'required|url:https',
        ]);
        $this->telegramHelper->setWebhook([
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
        $this->telegramHelper->deleteWebhook();
        return [
            'success' => true,
            'message' => "Webhook deleted successfully",
        ];
    }
}
