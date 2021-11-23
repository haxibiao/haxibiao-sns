<?php
namespace Haxibiao\Sns\Traits;

use App\Message;

trait MessageResolvers
{
    public function resolveSendMessage($root, array $args, $context, $info)
    {
        app_track_event("消息","发消息");
        $user    = getUser();
        $chat_id = $args['chat_id'];
        //兼容答赚
        $text = data_get($args, 'body.text');
        if (blank($text)) {
            //新breeze 消息 文本参数就是 text, 兼容旧接口参数名：message
            $text = data_get($args, 'text', data_get($args, 'message'));
        }

        //媒体地址，前端上传好的
        $url = data_get($args, 'url');
        //电影播放地址
        $play_url = data_get($args, 'play_url');
        $messageable_id   = data_get($args, 'messageable_id');
        $messageable_type = data_get($args, 'messageable_type');
        if(blank($text)){
            if($messageable_type == 'meetups'){
                $text = '[卡片分享]';
            }
        }
        return Message::sendMessage($user, $chat_id, $text, $url, $play_url,$messageable_id, $messageable_type);
    }

    public function resolveMessages($root, array $args, $context, $info)
    {
        $user   = getUser();
        $chatId = $args['chat_id'];

        //更新未读计数器
        $user->chats()->updateExistingPivot($chatId, ['unreads' => 0]);

        //原Graphql兼容
        $qb = Message::getMessagesQuery($chatId);

        $perPage = $args['count'] ?? 10;
        $page    = $args['page'] ?? 1;
        $offset  = ($page - 1) * $perPage;
        $limit   = $perPage;

        //更新阅读时间
        $qb->take($limit)->skip($offset)->update(['read_at' => now()]);

        return $qb;

    }
}
