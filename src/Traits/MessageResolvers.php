<?php
/**
 * @Author  guowei<gongguowei01@gmail.com>
 * @Data    2020/5/18
 * @Version
 */

namespace Haxibiao\Sns\Traits;

use App\Exceptions\UserException;
use App\Message;

trait MessageResolvers
{
    public function resolveSendMessage($root, array $args, $context, $info)
    {
        $user        = getUser();
        $chat_id     = $args['chat_id'];
        $messageBody = $args['body'];
        if (!isset($messageBody['text'])) {
            throw new UserException('发送失败,参数异常!');
        }
        return Message::sendMessage($user, $chat_id, $messageBody['text']);
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
