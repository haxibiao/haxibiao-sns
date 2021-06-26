<?php

namespace Haxibiao\Sns\Observers;

use Haxibiao\Sns\Message;

class MessageObserver
{

    public function created(Message $message)
    {
        event(new \Haxibiao\Breeze\Events\NewMessage($message));

        $user = $message->user;
        $chat = $message->chat;
        foreach ($chat->users as $chat_user) {
            if ($chat_user->id != $user->id) {
                //更新接受消息的用户消息未读数
                $chat_user->pivot->unreads = $user->pivot->unreads + 1;
                $chat_user->pivot->save();
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
