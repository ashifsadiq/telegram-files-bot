<?php
namespace App\Helpers;

use App\Http\Controllers\BotCommandsController;
use App\Models\CurrentQueue;
use App\Models\TelegramFolder;
use Illuminate\Http\Request;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramHelper
{
    /**
     * Create a new class instance.
     */
    protected $telegram;
    public function __construct()
    {
        $this->telegram = new Api(env('BOT_TOKEN'));
    }
    public function getMe()
    {
        $response = $this->telegram->getMe();
        return $response;
    }
    public function getChatMember($params = [])
    {
        return $this->telegram->getChatMember($params);
    }
    public function getUpdates()
    {
        return $this->telegram->getUpdates();
    }
    public function deleteWebhook()
    {
        return $this->telegram->deleteWebhook();
    }
    public function replyKeyboardMarkup()
    {
        $reply_markup = Keyboard::inlineButton()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button('Create a New Bot'),
            ])
            ->row([
                Keyboard::button('Send New Post to Subscribers'),
            ])
            ->row([
                Keyboard::button('Help'),
                Keyboard::button('Tutorials'),
            ]);
        return $reply_markup;
    }
    public function sendMessage($params = [])
    {
        $response = $this->telegram->sendMessage($params);
        return $response;
    }
    public function editMessageText($params = []): \Telegram\Bot\Objects\Message  | bool
    {
        $response = $this->telegram->editMessageText($params);
        return $response;
    }
    public function setWebhook($params = [])
    {
        $response = $this->telegram->setWebhook($params);

        return $response;
    }

    public function webhookInfo()
    {
        return $this->telegram->getWebhookInfo();
    }
    public function checkSubscription(Request $request)
    {
        $message = $request->input('message') ?? $request->input('edited_message');
        $chatId  = $message['chat']['id'] ?? null;

        if ($chatId && $chatId > 0) {
            $chatMember = $this->telegram->getChatMember([
                'chat_id' => env('COMMON_GROUP'),
                'user_id' => $chatId,
            ])['status'] ?? null;

            if ($chatMember === 'left' || $chatMember === null) {
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text'    => '🚫 Please join our group to use this bot: https://t.me/+ZODTR7r4us45M2U1',
                ]);

                // 🚫 Immediately stop further execution
                abort(200, 'User not subscribed');
            }
        }
    }
    public function handleBotCommands(Request $request): void
    {
        $botCommandHelper = new BotCommandHelper();
        $botCommandHelper->handleBotCommands(($request));
    }

    /**
     * @throws TelegramSDKException
     */
    public function deleteMessage($params = []): void
    {
        // chat_id, message_id
        $this->telegram->deleteMessage($params);
    }
    private function splitLastPart($string): array
    {
        // Find last slash position
        $lastSlashPos = strrpos($string, '/');

        // If no slash found
        if ($lastSlashPos === false) {
            return [$string, null];
        }

        // If slash is last character
        if ($lastSlashPos === strlen($string) - 1) {
            return [$string, null];
        }

        // Otherwise split into prefix + last part
        $prefix = substr($string, 0, $lastSlashPos + 1);
        $last   = substr($string, $lastSlashPos + 1);

        return [$prefix, $last];
    }
    public function handleCallbackQuery(Request $request)
    {
        $callback_query = $request->input('callback_query');
        if ($callback_query) {
            $message                   = $callback_query['message'];
            $chatId                    = $message['chat']['id'] ?? null;
            $callback_query_data       = $callback_query['data'];
            list($basePath, $lastPart) = $this->splitLastPart($callback_query_data);
            switch ($basePath) {
                case 'folder/page/':
                    {
                        $botCommandsController = new BotCommandsController();
                        return $botCommandsController->getFolders(
                            $chatId,
                            null,
                            $lastPart,
                            $message['message_id']
                        );
                    }
                case 'folder/open/':
                    {
                        $botCommandsController = new BotCommandsController();
                        $botCommandsController->getFolders(
                            $chatId,
                            $lastPart,
                            null,
                            $message['message_id']
                        );
                        break;
                    }
                case 'folder/back/':
                    {
                        $botCommandsController = new BotCommandsController();
                        $folder                = TelegramFolder::find($lastPart);
                        $botCommandsController->getFolders(
                            $chatId,
                            $folder->parent_folder_id,
                            null,
                            $message['message_id']
                        );
                        break;
                    }
                case 'folder/add/':
                    {
                        $botCommandsController = new BotCommandsController();
                        $botCommandsController->filesAddCurrentQueue(
                            chatId: $chatId,
                            messageId: $message['message_id'],
                            folderId: var_export($lastPart, true)
                        );
                        break;
                    }

                default: {
                        $botCommandsController = new BotCommandsController();
                        $this->sendMessage([
                            'chat_id' => $chatId,
                            'text'    => "Something gone wrong basePath: $basePath, lastPart: " . var_export($lastPart, true),
                        ]);
                    }
            }
            $this->telegram->answerCallbackQuery(['callback_query_id' => $callback_query['id']]);
            // $this->telegram->sendMessage([
            //     'chat_id'      => $chatId,
            //     'text'         => "Pressed - basePath: $basePath, lastPart: $lastPart",
            //     'reply_markup' => $reply_markup,
            // ]);
        }
        // abort(200, 'message');
    }
    public function manageTextSend(Request $request)
    {
        //     'inline_keyboard' => [
        //     ],
        // ];
        // for ($i = 1; $i <= 2; $i++) {
        //     array_push($keyboard['inline_keyboard'], [
        //         ['text' => "Button $i", 'callback_data' => "button_$i"],
        //     ]);
        // }
        $message           = $request->input('message');
        $chatId            = $message['chat']['id'] ?? null;
        $textGot           = $message['text'];
        $currentQueueCount = CurrentQueue::where(['user_id' => $chatId])->count();
        if ($currentQueueCount) {
            return $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text'    => 'Please Send Files to add press /stop to stop the process.',
            ]);
        }
        if ($chatId) {
            return $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text'    => $textGot,
                // 'reply_markup' => json_encode($keyboard),
            ]);
        }

    }
    public function managePhotoSend(Request $request)
    {
        $message = $request->input('message');
        $chatId  = $message['chat']['id'] ?? null;
        if ($chatId) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text'    => 'managePhotoSend',
            ]);
        }
    }
    public function manageVideoSend(Request $request)
    {
        $message = $request->input('message');
        $chatId  = $message['chat']['id'] ?? null;
        if ($chatId) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text'    => 'manageVideoSend',
            ]);
        }
    }
    public function manageDocumentSend(Request $request)
    {
        $message = $request->input('message');
        $chatId  = $message['chat']['id'] ?? null;
        if ($chatId) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text'    => 'manageDocumentSend',
            ]);
        }
    }
    public function manageAudioSend(Request $request)
    {
        $message = $request->input('message');
        $chatId  = $message['chat']['id'] ?? null;
        if ($chatId) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text'    => 'manageAudioSend',
            ]);
        }
    }
    public function manageVoiceSend(Request $request)
    {
        $message = $request->input('message');
        $chatId  = $message['chat']['id'] ?? null;
        if ($chatId) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text'    => 'manageVoiceSend',
            ]);
        }
    }
    public function manageStickerSend(Request $request)
    {
        $message = $request->input('message');
        $chatId  = $message['chat']['id'] ?? null;
        if ($chatId) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text'    => 'manageStickerSend',
            ]);
        }
    }
    public function manageLocationSend(Request $request)
    {
        $message = $request->input('message');
        $chatId  = $message['chat']['id'] ?? null;
        if ($chatId) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text'    => 'manageLocationSend',
            ]);
        }
    }
    public function manageUnknownSend(Request $request)
    {
        $message = $request->input('message');
        $chatId  = $message['chat']['id'] ?? null;
        if ($chatId) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text'    => 'manageUnknownSend',
            ]);
        }
    }
}
