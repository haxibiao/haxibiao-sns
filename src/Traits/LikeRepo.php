<?php

namespace Haxibiao\Sns\Traits;

use App\Comment;
use App\Contribute;
use App\User;
use Haxibiao\Sns\Like;
use Illuminate\Database\Eloquent\Relations\Relation;

trait LikeRepo
{
    public static function toggle(User $user, $type, $id)
    {
        $like = Like::firstOrNew([
            'user_id'      => $user->id,
            'likable_id'   => $id,
            'likable_type' => $type,
        ]);
        $likable   = $like->likable;
        $isNewLike = !isset($like->id);

        if (isset($like->id)) {
            //取消喜欢
            $like->forceDelete();
        } else {
            $like->save();
        }
        //新点赞需发送通知 触发几率奖励
        if ($isNewLike) {
            if (!empty($likable)) {
                //10%几率奖励当前用户贡献点
                $randNum = mt_rand(1, 10);
                if ($randNum > 5) {
                    if (method_exists(Contribute::class, 'rewardLike')) {
                        Contribute::rewardLike($user, $like);
                    } else if (method_exists(Contribute::class, 'rewardUserAction')) {
                        Contribute::rewardUserAction($user, 2);
                    }
                }

                //更新关联模型数据
                $likable->count_likes = $likable->likes()->count();
                $likable->save();
                static::likeReward($user, $like);
            }
        }

        return $like;
    }

    /**
     * 评论获得他人点赞(前3个赞),贡献+1（共三次）
     */
    protected static function likeReward($user, $like)
    {
        $likable = $like->likable;
        if ($likable instanceof Comment) {
            $comment = $likable;
            if ($comment->user_id != $user->id) {
                if ($comment->count_likes <= 3) {
                    Contribute::rewardUserComment($comment->user, $comment);
                }
            }
        }
    }

    public function likeUsers($input)
    {
        $modelString = Relation::getMorphedModel(data_get($input, 'likable_type'));
        $model       = $modelString::findOrFail(data_get($input, 'likable_id'));

        if (currentUser()) {
            $input['user_id'] = getUserId();
            $like             = self::firstOrNew($input);
            $data['is_liked'] = $like->id ? true : false;
        }
        $data['likes'] = $model->likes()
            ->with('user')
            ->paginate(10);
        $data['likesTotal'] = $model->likes()->count();
        return $data;
    }

}
