<?php

namespace Haxibiao\Sns\Observers;

use Haxibiao\Breeze\Exceptions\GQLException;
use Haxibiao\Sns\ChatUser;
use Haxibiao\Sns\Message;

class MessageObserver
{

    public function created(Message $message)
    {
        event(new \Haxibiao\Breeze\Events\NewMessage($message));
        $user = $message->user;
        $chat = $message->chat;
        throw_if(!$chat,GQLException::class,'该聊天消息没有哦！');
        $chat->update(['last_message_id' => $message->id]);
        foreach ($chat->users as $chat_user) {
            if ($chat_user->id != $user->id) {
                //更新接受消息的用户消息未读数
                if ($chatUserPivot = ChatUser::where(['chat_id' => $chat->id, 'user_id' => $chat_user->id])->first()) {
                    $chatUserPivot->update(['unreads' => $chatUserPivot->unreads + 1]);
                }
                //更新他的未读数缓存
                $chat_user->forgetUnreads();
            }
        }
    }

    public function updated(Message $message)
    {
        //
    }

    public function deleted(Message $message)
    {
        //
    }

    public function restored(Message $message)
    {
        //
    }

    public function forceDeleted(Message $message)
    {
        //
    }
}
