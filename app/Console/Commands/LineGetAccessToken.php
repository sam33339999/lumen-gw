<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Console\Input\InputOption;
use function PHPUnit\Framework\isNull;

class LineGetAccessToken extends Command
{
//    /**
//     * The console command name.
//     *
//     * @var string
//     */
//    protected $name = 'serve';
//
//    /**
//     * The console command description.
//     *
//     * @var string
//     */
//    protected $description = "Serve the application on the PHP development server";
//
//    /**
//     * Execute the console command.
//     *
//     * @return void
//     */
//    public function fire()
//    {
//        chdir(base_path('public'));
//
//        $host = $this->input->getOption('host');
//
//        $port = $this->input->getOption('port');
//
//        $base = $this->laravel->basePath();
//
//        $this->info("Lumen development server started on http://{$host}:{$port}/");
//
//        passthru('"'.PHP_BINARY.'"'." -S {$host}:{$port} \"{$base}\"/server.php");
//    }
//
//    /**
//     * Get the console command options.
//     *
//     * @return array
//     */
//    protected function getOptions()
//    {
//        return array(
//            array('host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on.', 'localhost'),
//
//            array('port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on.', 8000),
//        );
//    }

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'line:initial';

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

            if (
                empty($lineLoginId = env('LINE_LOGIN_ID')) ||
                empty($lineLoginSecret = env('LINE_LOGIN_SECRET')) ||
                empty($lineMsgId = env('LINE_MSG_ID')) ||
                empty($lineMsgSecret = env('LINE_MSG_SECRET'))
            ) {
                throw new \Exception('environment is not setting ...');
            }


            $willGetTokens = [
                $lineLoginId => $lineLoginSecret,
                $lineMsgId   => $lineMsgSecret,
            ];

            /*** @var Client $cli */
            $cli = app()->make(Client::class);

            foreach ($willGetTokens as $clientId => $clientSecret) {
                $result = $cli->post("https://api.line.me/v2/oauth/accessToken", [
                    'headers' => [
                        "Content-Type" => "application/x-www-form-urlencoded"
                    ],
                    'form_params' => [
                        'grant_type'    => 'client_credentials',
                        'client_id'     => $clientId,
                        'client_secret' => $clientSecret,
                    ]
                ])->getBody()->getContents();

                $result = json_decode($result, true);
                $accessToken = $result['access_token'];
                // $expiresIn = $result['expires_in'];
                // $tokenType = $result['token_type'];

                /** @var $redis \Predis\Client */
                $redis = Redis::connection();
                $redisKey = "accessToken-$clientId";
                $this->warn("\t-> setting redis key: `$redisKey`");
                $redis->set($redisKey, $accessToken);
            }
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
