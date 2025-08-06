<?php
namespace App\Helpers;

use Telegram\Bot\Api;
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
    public function getUpdates()
    {
        return $this->telegram->getUpdates();
    }
    public function deleteWebhook()
    {
        return $this->telegram->deleteWebhook();
    }
    public function replyKeyboardMarkup($params = [])
    {
        $reply2 = 
        $reply_markup = Keyboard::make()
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
            ])
            ->row([
                Keyboard::button('0'),
            ]);
        return $reply_markup;
    }
    public function sendMessage($params = [])
    {
        $response = $this->telegram->sendMessage($params);
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
}
