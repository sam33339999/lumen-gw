<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Predis\Client;

class LineServices
{
    const FOLLOW_STATUS_QUEUE = 'FOLLOW_STATUS_{{ channelId }}';
    const FOLLOW_FOLLOW_EVENT = 'FOLLOW_{{ channelId }}';
    const UNFOLLOW_UNFOLLOW_EVENT = 'UNFOLLOW_{{ channelId }}';
    const MESSAGE_EVENT = 'MESSAGE_{{ channelId }}';
    const JOIN_EVENT = 'JOIN_{{ channelId }}';
    const LEAVE_EVENT = 'LEAVE_EVENT_{{ channelId }}';

    /** @var $redis Client  */
    protected $redis;

    public function __construct() {
        $this->redis = Redis::connection();
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
    public function handleUnfollow(string $channelId, int $timestamp, array $source, string $mode)
    {
        $content = $source['userId'];
        $queueUnfollow = str_replace('{{ channelId }}', $channelId, self::UNFOLLOW_UNFOLLOW_EVENT);
        $queueStatus = str_replace('{{ channelId }}', $channelId, self::FOLLOW_STATUS_QUEUE);
        $this->redis->rpush($queueUnfollow, [$content]);
        $this->redis->rpush($queueStatus, ["unfollow-$timestamp-$content"]);
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
    public function handleFollow(string $channelId, int $timestamp, array $source, string $mode, string $replyToken)
    {
        $content = $source['userId'];
        $queueFollow = str_replace('{{ channelId }}', $channelId, self::FOLLOW_FOLLOW_EVENT);
        $queueStatus = str_replace('{{ channelId }}', $channelId, self::FOLLOW_STATUS_QUEUE);
        $this->redis->rpush($queueFollow, [$content]);
        $this->redis->rpush($queueStatus, ["follow-$timestamp-$content"]);
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
    public function handleMessage(string $channelId, int $timestamp, array $source, string $mode, string $replyToken)
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
    public function handleJoin(string $channelId, int $timestamp, array $source, string $mode, string $replyToken)
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
    public function handleLeave(string $channelId, int $timestamp, array $source, string $mode)
    {

    }
}
