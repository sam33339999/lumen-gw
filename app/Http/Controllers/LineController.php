<?php

namespace App\Http\Controllers;
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
        protected Logger $logger
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
        $eventFirst = $events[0];

        $type = $eventFirst['type'];
        $timestamp = $eventFirst['timestamp'];
        $source = $eventFirst['source'];
        $mode = $eventFirst['mode'];
        $replyToken = $eventFirst['replyToken'] ?? null;

        switch ($type) {
            case 'unfollow':
                $this->handleUnfollow($timestamp, $source, $mode);
                break;
            case 'leave':
                $this->handleLeave($timestamp, $source, $mode);
                break;

            case 'follow':
                $this->handleFollow($timestamp, $source, $mode, $replyToken);
                break;
            case 'message':
                $this->handleMessage($timestamp, $source, $mode, $replyToken);
                break;
            case 'join':
                $this->handleJoin($timestamp, $source, $mode, $replyToken);
                break;
        }

//        $this->logger->error(print_r($events, true));
    }

/** 封鎖
[0] => Array
    (
        [type] => unfollow
        [timestamp] => 1641314893615
        [source] => Array
            (
                [type] => user
                [userId] => Ua2c49d314904342d2999845fb1809a03
            )

        [mode] => active
    )
 */
    protected function handleUnfollow(int $timestamp, array $source, string $mode)
    {

    }

/** 解除封鎖
[0] => Array
    (
        [type] => follow
        [timestamp] => 1641314894495
        [source] => Array
            (
                [type] => user
                [userId] => Ua2c49d314904342d2999845fb1809a03
            )

        [replyToken] => 8af500ebf3a34b4abc4b4b5f201040ac
        [mode] => active
    )
*/
    protected function handleFollow(int $timestamp, array $source, string $mode, string $replyToken)
    {

    }

/** 機器人收到訊息
[0] => Array
    (
        [type] => message
        [message] => Array
            (
                [type] => text
                [id] => 15363985228164
                [text] => !!!
            )

        [timestamp] => 1641313328143
        [source] => Array
            (
                [type] => user
                [userId] => Ua2c49d314904342d2999845fb1809a03
                [?groupId] => Ce26af333cd92fd8428c0d94ae9320fc7
            )

        [replyToken] => 6742cdec884b4b4fb024a0660956b3b2
        [mode] => active
    )
*/
    protected function handleMessage(int $timestamp, array $source, string $mode, string $replyToken)
    {

    }


/** 加入群組
[0] => Array
    (
        [type] => join
        [timestamp] => 1641316888045
        [source] => Array
            (
                [type] => group
                [groupId] => Ce26af333cd92fd8428c0d94ae9320fc7
            )

        [replyToken] => 9ba95d8e06a24e56a43dac87a4425b58
        [mode] => active
    )
 */
    protected function handleJoin(int $timestamp, array $source, string $mode, string $replyToken)
    {

    }

/** 群組被踢
[0] => Array
    (
        [type] => leave
        [timestamp] => 1641317442053
        [source] => Array
            (
                [type] => group
                [groupId] => Ce26af333cd92fd8428c0d94ae9320fc7
            )

        [mode] => active
    )
 */
    protected function handleLeave(int $timestamp, array $source, string $mode)
    {

    }

}
