<?php
/**
 * @Author  guowei<gongguowei01@gmail.com>
 * @Data    2020/5/18
 * @Version
 */

namespace Haxibiao\Sns\Traits;

use App\Message;
use App\User;
use Haxibiao\Breeze\Exceptions\UserException;

trait MessageRepo
{
    /**
     * 发送消息
     *
     * @param User $user
     * @param string $chatId
     * @param array $messageBody
     * @return Message
     * @throws \Exception
     */
    public static function sendMessage(User $user, int $chatId, string $messageBody)
    {
        //目前只有文本消息
        $message = (new Message)->fill([
            'user_id' => $user->id,
            'body'    => ['text' => $messageBody],
            'chat_id' => $chatId,
        ]);

        $chat     = $message->chat;
        $otherIds = array_diff($chat->uids, [$user->id]);
        if (count($otherIds) > 0) {
            $other      = User::whereIn('id', $otherIds)->first();
            $myBlack    = $user->userBlacks()->where('blackable_id', $other->id)->first();
            $otherBlack = $other->userBlacks()->where('blackable_id', $user->id)->first();
            throw_if($myBlack || $otherBlack, UserException::class, "发送失败，您已被对方拉黑");
        }

        if (is_null($chat) || !$chat->containsMembers($user->id)) {
            throw new \Exception('发送失败,未成为好友关系!');
        }
        $message->save();

        return $message;
    }

    public static function getMessages(User $user, $chatId, $limit = 10, $offset = 0)
    {
        //更新未读计数器
        $user->chats()->updateExistingPivot($chatId, ['unreads' => 0]);

        //原Graphql兼容
        $qb       = Message::getMessagesQuery($chatId);
        $messages = $qb
            ->take($limit)
            ->skip($offset)
            ->get();

        //更新阅读时间
        $qb->take($limit)->skip($offset)->update(['read_at' => now()]);

        return $messages;
    }

    public static function getMessagesQuery($chatId)
    {
        return Message::with('user')->where('chat_id', $chatId)
            ->latest('id');
    }
}
