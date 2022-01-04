<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Redis;

// https://www.oxxostudio.tw/articles/201806/line-push-message.html

class LinePushMessage extends Command
{
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
        try {

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