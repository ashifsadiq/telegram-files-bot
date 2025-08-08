<?php
namespace App\Console\Commands;

use App\Helpers\TelegramHelper;
use Illuminate\Console\Command;

class TelegramWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    protected $signature = 'tg:webhook {url}';

    public function handle()
    {
        $telegramHelper = new TelegramHelper();
        $url            = $this->argument('url');
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            if (! str_ends_with($url, '/')) {
                $url .= "/";
            }
            $telegramHelper->setWebhook([
                'url' => $url . 'api/telegram/webhooks/inbound',
            ]);
            $this->info("Webhook updated URL: $url");
        }
    }

}
