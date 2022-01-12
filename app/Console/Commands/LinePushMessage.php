<?php

namespace App\Console\Commands;

use App\Exceptions\JsonParseException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

// https://www.oxxostudio.tw/articles/201806/line-push-message.html

class LinePushMessage extends Command
{
    /** @var $redis \Predis\Client */
    protected $redis;

    /** @var Client $client */
    protected $client;

    public function __construct(Client $client) {
        parent::__construct();
        $this->redis = Redis::connection();
        $this->client = $client;
    }
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'line:push-message';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'line 主動推播訊息';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        while (true) {
            try {
                $channelId = env('MESSAGE_CHANNEL_ID');
                $token = $this->redis->get("LINE_TOKEN_$channelId");

                $contentJson = $this->redis->blpop("PUSH_LIST_$channelId", 600);
                $contentJson = $contentJson[1];
                if (! $this->verifyFormat($contentJson)) {
                    throw new \Exception('content error: ' . $contentJson);
                }

                (clone $this->client)->post('https://api.line.me/v2/bot/message/multicast', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer $token",
                    ],
                    'body' => $contentJson,
                ]);
            } catch (\Exception | GuzzleException  $exception) {
                Log::error($exception->getMessage());
            }
        }
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }

    protected function verifyFormat($pushJson): bool
    {
        if (! $pushJson) return false;

        $data = json_decode($pushJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        if (count($data['to'] ?? []) === 0) {
            return false;
        }

        if (count($data['messages'] ?? []) === 0) {
            return false;
        }
        return true;
    }
}
