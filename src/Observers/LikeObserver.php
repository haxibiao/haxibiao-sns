<?php

namespace Haxibiao\Sns\Observers;

use Haxibiao\Breeze\Events\NewLike;
use Haxibiao\Sns\Like;

class LikeObserver
{
    public function created(Like $like)
    {
        $user = $like->user;
        if ($profile = $user->profile) {
            $profile->count_likes = $user->likes()->count();
            $profile->save();
        }

        //更新被喜欢对象的计数（刷新时间，更新排序）
        if ($likable = $like->likable) {
            $likable->count_likes = $likable->count_likes + 1;
            $likable->save();

            //通知用户
            if ($likable->user->id != $user->id) {
                event(new \Haxibiao\Breeze\Events\NewLike($like));
            }
        }

        //检查点赞任务是否完成了
        $user->reviewTasksByClass(get_class($like));
        app_track_event('用户', '点赞');
    }

    public function deleted(Like $like)
    {
        if ($likable = $like->likable) {
            if($likable->count_likes == 0){
                $count_likes = $likable->count_lies;
            }else{
                $count_likes = $likable->count_likes - 1;
            }
            $likable->count_likes = $count_likes;
            $likable->save();
        } 

        $user = $like->user;
        if ($profile = $user->profile) {
            $profile->count_likes = $user->likes()->count();
            $profile->save();
        }
    }
}
