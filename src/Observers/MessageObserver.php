<?php

namespace Haxibiao\Sns\Observers;

use Haxibiao\Sns\Message;

class MessageObserver
{

    public function created(Message $message)
    {
        event(new \Haxibiao\Breeze\Events\NewMessage($message));

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
