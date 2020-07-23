<?php

namespace Haxibiao\Sns\Observers;


use Haxibiao\Sns\Follow;

class FollowObserver
{
    /**
     * Handle the follow "created" event.
     *
     * @param  \App\Follow  $follow
     * @return void
     */
    public function created(Follow $follow)
    {
        if ($follow->followed_type == 'users') {
            //更新用户的关注数 //FIXME: 以前从来没count 过，需要fixdata count一次做基础...
            $user = $follow->user;
            $user->profile->increment('follows_count');

            //更新被关注用户的粉丝数
            if ($followed = $follow->followed) {
                $followed->profile->increment('followers_count');
            }
        }
    }

    /**
     * Handle the follow "updated" event.
     *
     * @param  \App\Follow  $follow
     * @return void
     */
    public function updated(Follow $follow)
    {
        //
    }

    /**
     * Handle the follow "deleted" event.
     *
     * @param  \App\Follow  $follow
     * @return void
     */
    public function deleted(Follow $follow)
    {
        if ($follow->followed_type == 'users') {
            //更新用户的关注数
            $user = $follow->user;
            $user->profile->decrement('follows_count');

            //更新被关注用户的粉丝数
            if ($followed = $follow->followed) {
                $followed->profile->decrement('followers_count');
            }
        }
    }

    /**
     * Handle the follow "restored" event.
     *
     * @param  \App\Follow  $follow
     * @return void
     */
    public function restored(Follow $follow)
    {
        //
    }

    /**
     * Handle the follow "force deleted" event.
     *
     * @param  \App\Follow  $follow
     * @return void
     */
    public function forceDeleted(Follow $follow)
    {
        //
    }
}
