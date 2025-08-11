<?php
namespace App\Helpers;

use App\Http\Controllers\BotCommandsController;
use App\Models\TelegramFolder;
use App\Models\UploadingQueue;
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
                        $botCommandsController->getFolders(
                            $chatId,
                            null,
                            $lastPart,
                            $message['message_id']
                        );
                        break;
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
                            folderId: $lastPart
                        );
                        break;
                    }
                case 'folder/done/':{
                        $botCommandsController = new BotCommandsController();
                        $botCommandsController->doneUploadingQueueFiles($chatId);
                        break;
                    }
                case 'manageFolders/add/':{
                        $botCommandsController = new BotCommandsController();
                        break;
                    }
                case 'manageFolders/page/':{
                        $botCommandsController = new BotCommandsController();
                        $botCommandsController->manageFolders(
                            $chatId,
                            null,
                            $lastPart,
                            $message['message_id']
                        );
                        break;
                    }
                case 'manageFolders/back/':{
                        $botCommandsController = new BotCommandsController();
                        $folder                = TelegramFolder::find($lastPart);
                        $botCommandsController->manageFolders(
                            $chatId,
                            $folder->parent_folder_id,
                            null,
                            $message['message_id']
                        );
                        break;
                    }
                case 'manageFolders/open/':{
                        $botCommandsController = new BotCommandsController();
                        $botCommandsController->manageFolders(
                            $chatId,
                            $lastPart,
                            null,
                            $message['message_id'],
                        );
                        break;
                    }
                case 'manageFolders/view/':{
                        $botCommandsController = new BotCommandsController();
                        $botCommandsController->getFiles(
                            $chatId,
                            $lastPart,
                            null,
                            $message['message_id'],
                        );
                        break;
                    }
                case "getFiles/page/":{
                        $str = $lastPart; // Test with "-1010" too

                        $left  = null;
                        $right = null;

                        if (strpos($str, '-') === 0) {
                            // Case: starts with a dash → left is null, right is everything after dash
                            $right = substr($str, 1);
                        } elseif (strpos($str, '-') !== false) {
                            // Case: contains a dash, split into two parts
                            [$left, $right] = explode('-', $str, 2);
                        } else {
                            // No dash at all
                            $left = $str;
                        }
                        $this->sendMessage([
                            'chat_id' => $chatId,
                            'text'    => "left: $left, right: $right",
                        ]);
                        $botCommandsController = new BotCommandsController();
                        $botCommandsController->getFiles(
                            $chatId,
                            $left,
                            $right,
                            $message['message_id'],
                        );
                        break;
                    }
                default: {
                        $botCommandsController = new BotCommandsController();
                        $this->sendMessage([
                            'chat_id' => $chatId,
                            'text'    => "Something gone wrong basePath: $basePath, lastPart: " . var_export($lastPart, true),
                        ]);
                        break;
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
    /**
     *       'chat_id'                     => '',                      // int|string       - Required. Unique identifier for the target chat or username of the target channel (in the format "@channelusername")

     *       'photo'                       => InputFile::file($file),  // InputFile|string - Required. Photo to send. Pass a file_id as String to send a photo that exists on the Telegram servers (recommended), pass an HTTP URL as a String for Telegram to get a photo from the Internet, or upload a new photo using multipart/form-data.

     *       'caption'                     => '',                      // string           - (Optional). Photo caption (may also be used when resending photos by file_id), 0-200 characters

     *       'parse_mode'                  => '',                      // string           - (Optional). Send Markdown or HTML, if you want Telegram apps to show bold, italic, fixed-width text or inline URLs in the media caption.

     *       'caption_entities'            => '',                      // array            - (Optional). List of special entities that appear in the caption, which can be specified instead of parse_mode

     *       'disable_notification'        => '',                      // bool             - (Optional). Sends the message silently. iOS users will not receive a notification, Android users will receive a notification with no sound.

     *       'protect_content'             => '',                      // bool             - (Optional). Protects the contents of the sent message from forwarding and saving

     *       'reply_to_message_id'         => '',                      // int              - (Optional). If the message is a reply, ID of the original message

     *       'allow_sending_without_reply' => '',                      // bool       - (Optional). Pass True, if the message should be sent even if the specified replied-to message is not found

     *       'reply_markup'                => '',                      // string           - (Optional). Additional interface options. A JSON-serialized object for an inline keyboard, custom reply keyboard, instructions to remove reply keyboard or to force a reply from the user.

     */
    public function sendPhoto($params = [])
    {
        $this->telegram->sendPhoto($params);
    }
    public function manageTextSend(Request $request)
    {
        $message           = $request->input('message');
        $chatId            = $message['chat']['id'] ?? null;
        $textGot           = $message['text'];
        $currentQueueCount = UploadingQueue::where(['user_id' => $chatId])->count();
        if ($currentQueueCount) {
            return $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text'    => 'Please Send Files to add press /cancel to stop the process.',
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
        $type           = 'photo';
        $message        = $request->input('message');
        $chatId         = $message['chat']['id'] ?? null;
        $uploadingQueue = UploadingQueue::where(['user_id' => $chatId])->first();
        $photo          = end($message[$type]);
        $caption        = $message['caption'] ?? null;
        if ($uploadingQueue && isset($photo)) {
            $saveUploadingQueueFiles = new BotCommandsController();
            return $saveUploadingQueueFiles->saveUploadingQueueFiles(
                array_merge(
                    $photo,
                    [
                        'caption'             => $caption,
                        'type'                => $type,
                        'uploading_queues_id' => $uploadingQueue->id,
                        'chat_id'             => $chatId,
                    ]
                )
            );
        }
        if ($chatId) {
            return $this->telegram->sendMessage([
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
