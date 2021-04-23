<?php

namespace Haxibiao\Sns\Observers;

use Haxibiao\Breeze\Events\NewLike;
use Haxibiao\Breeze\Listeners\SendNewLikeNotification;
use Haxibiao\Sns\Like;

class LikeObserver
{
    public function created(Like $like)
    {
        $user                 = $like->user;
        $profile              = $user->profile;
        $profile->count_likes = $user->likes()->count();
        $profile->save();

        //更新被喜欢对象的计数（刷新时间，更新排序）
        if ($likable = $like->likable) {
            $likable->count_likes = $likable->count_likes + 1;
            $likable->save();
        }

        //检查点赞任务是否完成了
        $user->reviewTasksByClass(get_class($like));

        app_track_event('用户', '点赞');
        dispatch(new SendNewLikeNotification(new NewLike($like)));
    }

    public function deleted(Like $like)
    {
        $user                       = $like->user;
        $user->profile->count_likes = $user->likes()->count();
        $user->profile->save();
    }
}
