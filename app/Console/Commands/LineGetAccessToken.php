<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Redis;

class LineGetAccessToken extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'line:initial {channel_id} {channel_secret}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'register line token into redis';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        try {
            $this->info("line initial ...");

            $channelId = $this->argument('channel_id');
            $channelSecret = $this->argument('channel_secret');

            /*** @var Client $cli */
            $cli = app()->make(Client::class);
            $result = $cli->post("https://api.line.me/v2/oauth/accessToken", [
                'headers' => [
                    "Content-Type" => "application/x-www-form-urlencoded"
                ],
                'form_params' => [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $channelId,
                    'client_secret' => $channelSecret,
                ]
            ])->getBody()->getContents();

            $result = json_decode($result, true);
            $accessToken = $result['access_token'];

            /** @var $redis \Predis\Client */
            $redis = Redis::connection();
            $redisChannelAccessToken = "LINE_TOKEN_$channelId";
            $redisChannelSecret = "LINE_CHANNEL_SECRET_$channelId";

            $this->warn("\t-> setting redis key: `$redisChannelAccessToken`");
            $redis->set($redisChannelAccessToken, $accessToken);
            $this->warn("\t-> setting redis key: `$redisChannelSecret`");
            $redis->set($redisChannelSecret, $channelSecret);

            return 0;
        } catch (\Exception | GuzzleException  $exception) {
            $this->error("Failed to initial line access_token ! because: " . $exception->getMessage());
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
}
