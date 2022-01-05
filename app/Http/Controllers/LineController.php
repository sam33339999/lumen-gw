<?php

namespace App\Http\Controllers;
use App\Services\LineServices;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;

class LineController extends Controller
{
    const ACCESS_LOG_FORMAT =<<<LOGGER
URI: {{ __URI__ }}({{ __METHOD__ }})
--- HEADERS: {{ __HEADERS__ }}
*** REQUEST_JSON: {{ __REQUEST_JSON__ }}
LOGGER;
// "";
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected Logger $logger,
        protected LineServices $lineServices
    ) { /** PHP 8 Syntax - DI */ }

    public function verify(string $channelId, Request $request)
    {
        $logStr = str_replace([
            '{{ __URI__ }}', '{{ __METHOD__ }}', '{{ __HEADERS__ }}', '{{ __REQUEST_JSON__ }}'
        ], [
            $request->fullUrl(), 'GET', json_encode($request->headers), json_encode($request->all())
        ], self::ACCESS_LOG_FORMAT);
        $this->logger->info($logStr);
    }

    public function hook(string $channelId, Request $request)
    {
        $logStr = str_replace([
            '{{ __URI__ }}', '{{ __METHOD__ }}', '{{ __HEADERS__ }}', '{{ __REQUEST_JSON__ }}'
        ], [
            $request->fullUrl(), 'POST', json_encode($request->headers), json_encode($request->all())
        ], self::ACCESS_LOG_FORMAT);
        $this->logger->info($logStr);

        $events = $request->get('events');

        if (empty($events)) { // maybe verify
            return response()->json(['success' => 'true']);
        } else {
            $eventFirst = $events[0];

            $type = $eventFirst['type'];
            $timestamp = $eventFirst['timestamp'];
            $source = $eventFirst['source'];
            $mode = $eventFirst['mode'];
            $replyToken = $eventFirst['replyToken'] ?? null;

            switch ($type) {
                case 'unfollow':
                    $this->lineServices->handleUnfollow($channelId, $timestamp, $source, $mode);
                    break;
                case 'leave':
                    $this->lineServices->handleLeave($channelId, $timestamp, $source, $mode);
                    break;

                case 'follow':
                    $this->lineServices->handleFollow($channelId, $timestamp, $source, $mode, $replyToken);
                    break;
                case 'message':
                    $this->lineServices->handleMessage($channelId, $timestamp, $source, $mode, $replyToken);
                    break;
                case 'join':
                    $this->lineServices->handleJoin($channelId, $timestamp, $source, $mode, $replyToken);
                    break;
            }
        }

        $this->logger->error(print_r($events, true));
    }


}
