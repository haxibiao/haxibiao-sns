<?php

namespace Haxibiao\Sns\Traits;

use App\Like;

trait Likeable
{
    public static function bootLikeable()
    {
        static::deleting(function ($model) {
            if ($model->forceDeleting) {
                //删除被喜欢的记录
                $model->likes()->delete();
                $model->count_likes = 0;
                $model->save();

                //FIXME: 喜欢过得用户，全部要更新数据完整性 count_likes? 意义不大
            }
        });
    }

    public function likedTableIds($likavleType, $likableIds)
    {
        return $this->likes()->select('likable_id')
            ->whereIn('likable_id', $likableIds)
            ->where('likable_type', $likavleType)
            ->get()
            ->pluck('likable_id');
    }

    // FIXME 暂时注释避免冲突
    // 2021-1-25更新： 打开注释。
    // 原因：获取用户喜欢的资源。避免更新包后下列页面报错：
    // https://diudie.com/user/293/followed-categories
    // https://diudie.com/user/293/likes
    // https://diudie.com/user/293/followed-collections
    public function likes()
    {
        if ($this->getMorphClass() == 'users') {
            return $this->hasMany(Like::class);
        }
        return $this->morphMany(Like::class, 'likable');
    }

    public function getLikedAttribute()
    {
        //快速推荐模式，不获取历史已喜欢状态
        if (request('fast_recommend_mode')) {
            return false;
        }
        if (checkUser()) {
            return $this->isLiked();
        }
        return false;
    }

    // 兼容旧接口用
    public static function likedPosts($user, $posts)
    {
        $postIds = $posts->pluck('id');
        if (count($postIds) > 0) {
            $likedIds = $user->likedTableIds('posts', $postIds);
            //更改liked状态
            $posts->each(function ($post) use ($likedIds) {
                $post->liked = $likedIds->contains($post->id);
            });
        }

        return $posts;
    }

    // 兼容旧接口用
    public function getLikedIdAttribute()
    {
        if ($user = getUser(false)) {
            $like = $user->likedArticles()->where('likable_id', $this->id)->first();
            return $like ? $like->id : 0;
        }
        return 0;
    }

    public function likeIt($user = null)
    {
        if (is_null($user)) {
            $user = getUser();
        }

        if ($user) {
            $like = $this->likes()
                ->where('user_id', '=', $user->id)
                ->first();

            if ($like) {
                return;
            }

            $like          = new Like();
            $like->user_id = $user->id;
            $this->incrementLikeCount();
            return $this->likes()->save($like);
        }
    }

    public function unLikeIt($user = null)
    {
        if (is_null($user)) {
            $user = getUser();
        }

        if ($user) {
            $like = $this->likes()
                ->where('user_id', '=', $user->id)
                ->first();

            if (!$like) {return;}

            $like->delete();
            $this->decrementLikeCount();
            return $like;
        }
    }

    public function toggleLike($user = null)
    {
        return $this->isLiked($user) ? $this->unLikeIt($user) : $this->likeIt($user);
    }

    public function isLiked($user = null)
    {
        return (bool) $this->likes()
            ->where('user_id', '=', $user ? $user->id : getUser()->id)
            ->count();
    }

    public function decrementLikeCount($count = 1)
    {
        if ($count <= 0) {return;}

        $this->count_likes = $this->count_likes - $count;
        if ($this->count_likes < 0) {
            $this->count_likes = 0;
        }
        $this->save();
    }

    public function incrementLikeCount($count = 1)
    {
        if ($count <= 0) {return;}
        $this->count_likes = $this->count_likes + $count;
        $this->save();
    }

    public function getCountLikesAttribute()
    {
        return $this->likes()->count();
    }
}
