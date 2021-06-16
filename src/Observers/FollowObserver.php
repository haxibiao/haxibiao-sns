<?php

namespace Haxibiao\Sns\Observers;

use Haxibiao\Breeze\Events\NewFollow;
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
        event(new NewFollow($follow));

        //FIXME: 资料里关注的2个计数，答赚字段习惯_count结尾的后面改成sns的count_开头
        $count_follows    = project_is_dtzq() ? 'follows_count' : 'count_follows'; //关注
        $count_followings = project_is_dtzq() ? 'followers_count' : 'count_followings'; //粉丝

        if ('users' == $follow->followable_type) {
            //更新用户的关注数
            //FIXME: 以前从来没count 过，需要fixdata count一次做基础...
            $user = $follow->user;
            $user->profile->increment($count_follows);
            //更新被关注用户的粉丝数
            if ($followable = $follow->followable) {
                $followable->profile->increment($count_followings);
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
        //FIXME: 资料里关注的2个计数，答赚字段习惯_count结尾的后面改成sns的count_开头
        $count_follows    = project_is_dtzq() ? 'follows_count' : 'count_follows'; //关注
        $count_followings = project_is_dtzq() ? 'followers_count' : 'count_followings'; //粉丝

        if ('users' == $follow->followable_type) {
            //更新用户的关注数
            $user = $follow->user;
            $user->profile->decrement($count_follows);

            //更新被关注用户的粉丝数
            if ($followable = $follow->followable) {
                $followable->profile->decrement($count_followings);
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
