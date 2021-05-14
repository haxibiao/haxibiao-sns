<?php

namespace Haxibiao\Sns\Listeners;

use App\User;
use Haxibiao\Breeze\Events\NewFollow;

class SendFollowNotification
{
    // public $queue = 'listeners';
    public $delay = 10;

    public $follow;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  NewFollow  $event
     * @return void
     */
    public function handle(NewFollow $event)
    {
        $this->follow = $event->follow;

        //通知用户
        if ($this->follow->followed instanceof User) {
            $this->follow->followed->notify((new \App\Notifications\UserFollowNotification($this->follow))->delay(now()->addMinute(5)));
        }
    }
}
