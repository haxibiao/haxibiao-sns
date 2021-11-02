<?php
namespace Haxibiao\Sns\Observers;

use Haxibiao\Sns\Chat;

class ChatObserver
{
    /**
     * Handle the chat "created" event.
     *
     * @param  \App\Chat  $chat
     * @return void
     */
    public function created(Chat $chat)
    {
        if ($chat->users()->count() == 0) {
            return;
        }
        $chat->users()->sync($chat->uids);
    }

    /**
     * Handle the chat "updated" event.
     *
     * @param  \App\Chat  $chat
     * @return void
     */
    public function updated(Chat $chat)
    {
        if ($chat->users()->count() == 0) {
            return;
        }
        $chat->users()->sync($chat->uids);
    }

    /**
     * Handle the chat "deleted" event.
     *
     * @param  \App\Chat  $chat
     * @return void
     */
    public function deleted(Chat $chat)
    {
        //
    }

    /**
     * Handle the chat "restored" event.
     *
     * @param  \App\Chat  $chat
     * @return void
     */
    public function restored(Chat $chat)
    {
        //
    }

    /**
     * Handle the chat "force deleted" event.
     *
     * @param  \App\Chat  $chat
     * @return void
     */
    public function forceDeleted(Chat $chat)
    {
        //
    }
}