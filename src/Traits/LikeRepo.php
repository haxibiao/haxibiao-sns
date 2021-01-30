<?php

namespace Haxibiao\Sns\Traits;

use App\Comment;
use App\Contribute;
use App\User;
use Haxibiao\Breeze\Events\NewLike;
use Haxibiao\Sns\Like;
use Illuminate\Database\Eloquent\Relations\Relation;

trait LikeRepo
{

    public function toggleLike($input)
    {
        //只能简单创建
        $user         = getUser();
        $likable_id   = data_get($input, 'liked_id', data_get($input, 'likable_id'));
        $likable_type = data_get($input, 'liked_type', data_get($input, 'likable_type'));
        $like         = Like::firstOrNew([
            'user_id'      => $user->id,
            'likable_id'   => $likable_id,
            'likable_type' => $likable_type,
        ]);
        //取消喜欢
        if (($input['undo'] ?? false) || $like->id) {
            $like->delete();
            $liked_flag = false;
        } else {
            $like->save();
            $liked_flag = true;
        }
        $like_obj = $like->liked;
        if ($likable_type == 'comments') {
            $like_obj->liked = $liked_flag;
        }
        return $like_obj;
    }

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
            if (!empty($likable)) {
                //通知用户
                if ($likable->user->id != $user->id) {
                    event(new NewLike($like));
                }
                //10%几率奖励当前用户贡献点
                $randNum = mt_rand(1, 10);
                if ($randNum == 1) {
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

    public function likeUsers($input)
    {
        $modelString = Relation::getMorphedModel(data_get($input, 'likable_type'));
        $model       = $modelString::findOrFail(data_get($input, 'likable_id'));

        if (checkUser()) {
            $user             = getUser();
            $input['user_id'] = $user->id;
            $like             = self::firstOrNew($input);
            $data['is_liked'] = $like->id;
        }
        $data['likes'] = $model->likes()
            ->with('user')
            ->paginate(10);
        return $data;
    }

}
