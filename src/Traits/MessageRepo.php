<?php
namespace Haxibiao\Sns\Traits;

use App\Message;
use App\User;

trait MessageRepo
{
    /**
     * 发送消息
     *
     * @param User $user
     * @param string $chatId
     * @param string $text
     * @param string $url 图片/音频/视频
     * @return Message
     */
    public static function sendMessage(User $user, int $chatId, string $text, String $url = null, String $play_url = null, Int $messageable_id=null, String $messageable_type=null)
    {
        $type = Message::TEXT_TYPE;
        $body = ['text' => $text];
        //开始支持语音
        if ($url) {
            $body['url'] = $url;
            if (str_contains($url, '.jpg') || str_contains($url, '.png')) {
                $type = Message::IMAGE_TYPE;
            }
            if (str_contains($url, '.mp3')) {
                $type = Message::AUDIO_TYPE;
            }
            if (str_contains($url, '.mp4')) {
                $type = Message::VIDEO_TYPE;
            }
            //电影卡片
            if ($play_url) {
                $body['play_url'] = $play_url;
                $type             = Message::MOVIE_CARD_TYPE;
            }
        }
        $message = (new Message)->fill([
            'user_id' => $user->id,
            'type'    => $type,
            'body'    => $body,
            'chat_id' => $chatId,
        ]);
        // 约单卡片功能
        if(!blank($messageable_id)){
            if($messageable_type == 'meetups'){
                data_set($message,'type',static::MEETUP_CARD_TYPE);
                data_set($message,'messageable_id',$messageable_id);
                data_set($message,'messageable_type','articles');
            }
        }
        //TODO: 拉黑功能暂时不着急
        // $chat     = $message->chat;
        // $otherIds = array_diff($chat->uids, [$user->id]);
        // if (count($otherIds) > 0) {
        //     $other      = User::whereIn('id', $otherIds)->first();
        //     $otherBlack = $other->userBlacks()->where('blackable_id', $user->id)->first();
        //     throw_if($otherBlack, UserException::class, "发送失败，您已被对方拉黑");
        // }

        //无论成败，先把消息保存上
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
