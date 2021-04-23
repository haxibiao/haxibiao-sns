<?php

namespace Haxibiao\Sns\Observers;

use Haxibiao\Breeze\Events\NewMessage;
use Haxibiao\Breeze\Listeners\SendNewMessageNotification;
use Haxibiao\Sns\Message;

class MessageObserver
{

    public function created(Message $message)
    {
        dispatch(new SendNewMessageNotification(new NewMessage($message)));
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
