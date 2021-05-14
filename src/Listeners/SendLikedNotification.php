<?php

namespace Haxibiao\Sns\Listeners;

use Haxibiao\Breeze\Events\NewLike;
use Haxibiao\Breeze\Notifications\LikedNotification;

class SendLikedNotification
{
    // public $queue = 'listeners';
    public $delay = 10;

    protected $like;

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
     * @param  NewLike  $event
     * @return void
     */
    public function handle(NewLike $event)
    {
        $this->like = $event->like;
        $likable    = $this->like->likable;

        if (!is_null($likable)) {
            $likableUser = $likable->user;
            if (!is_null($likableUser)) {
                $likable->user->notify(new LikedNotification($this->like));
            }
        }
    }
}
