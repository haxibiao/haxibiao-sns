<?php

namespace Haxibiao\Sns\Traits;


use App\Comment;
use App\Contribute;
use App\Events\NewLike;
use App\User;

trait LikeRepo
{

    public static function toggle(User $user, $type, $id)
    {
        //只能简单创建
        $like = static::firstOrNew([
            'user_id'      => $user->id,
            'likable_id'   => $id,
            'likable_type' => $type,
        ]);
        $likable   = $like->likable;
        $isNewLike = !isset($like->id);

        if (isset($like->id)) {
            $like->forceDelete();
        } else {
            $like->save();
        }

        //新点赞需发送通知 触发几率奖励
        if ($isNewLike) {
            //通知用户
            if ($likable->user->id != $user->id) {
                event(new NewLike($like));
            }

            //10%几率奖励当前用户贡献点
            $randNum = mt_rand(1, 10);
            if ($randNum == 1) {
                Contribute::rewardLike($user, $like);
            }
        }

        //更新关联模型数据
        $likable->count_likes = $likable->likes()->count();
        $likable->save();

        static::likeReward($user, $like);

        return $like;
    }

    protected static function likeReward($user, $like)
    {
        $likable = $like->likable;

        //评论获得他人点赞(前3个赞),贡献+1（共三次）
        if ($likable instanceof Comment) {
            $comment = $likable;
            if ($comment->user_id != $user->id) {
                if ($comment->count_likes <= 3) {
                    Contribute::rewardUserComment($comment->user, $comment);
                }
            }
        }
    }
}
