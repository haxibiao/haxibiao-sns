<?php

namespace Haxibiao\Sns\Traits;

use App\Follow;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Followable
{
    public static function bootFollowable()
    {
        static::deleting(function ($model) {
            //清理冗余的关注记录
            if ($model->forceDeleting) {
                $model->followables()->delete();
                $model->save();
                //FIXME: 更新所有相关用户的关注数？意义没脏数据崩页面大
                // $model->profile->count_follows = 0;
            }
        });
    }

    /**
     * 对象的被关注记录 = followers
     */
    public function followables(): MorphMany
    {
        return $this->morphMany(Follow::class, 'followable');
    }

    /**
     * 用户的全部关注列表 = followings
     */
    public function follows()
    {
        return $this->hasMany(Follow::class);
    }

    //这个语义也要重构
    public function getFollowableAttribute()
    {
        if (checkUser()) {
            return static::isFollowable($this);
        }
        return false;
    }

    //是否已经关注过当前model (应该语义上是  isFollowed)
    public function isFollowable($model = null)
    {
        // $methodName = config('haxibiao-sns.follow.passive.' . get_class($model));
        // $count      = (bool) $model->$methodName()
        //     ->where('user_id', getUser()->id)
        //     ->count();
        // return $count;

        //FIXME: 待优化这样的sns able 是否逻辑, 补充好索引，早期都是一个query搞定mysql
        return false;
    }

    /**
     * @return mixed
     * 1. 粉丝列表：user 作为CanBeFollow对象
     * 2. 维护命名冲突而冗余的关系(follows冲突)
     * 3. 在其他的CanBeFollow对象中（如Category，粉丝列表的关系名为 follows
     */
    public function followers(): MorphMany
    {
        return $this->morphMany(Follow::class, 'followable');
    }

    /**
     * 用户的关注列表，兼容工厂系 = follows
     */
    public function followings()
    {
        return $this->hasMany(Follow::class);
    }

    //关注的集合
    public function followCollections()
    {
        return $this->hasMany(Follow::class)
            ->where('followable_type', 'collections');
    }

    //关注的分类
    public function followCategories()
    {
        return $this->hasMany(Follow::class)
            ->where('followable_type', 'categories');
    }

    //关注当前model
    public function followIt($model = null)
    {
        $methodName = config('haxibiao-sns.follow.passive.' . get_class($model));
        app_track_event('用户', "关注");
        if (checkUser()) {
            $user   = getUser();
            $follow = $model->$methodName()
                ->where('user_id', '=', $user->id)
                ->first();
            if ($follow) {
                return;
            }

            $follow          = new Follow();
            $follow->user_id = $user->id;
            $save            = $model->$methodName()->save($follow);
            return $save;
        }
    }

    public function unFollowIt($model = null)
    {
        $methodName = config('haxibiao-sns.follow.passive.' . get_class($model));
        if (checkUser()) {
            $user   = getUser();
            $follow = $model->$methodName()
                ->where('user_id', '=', $user->id)
                ->first();
            if (!$follow) {return;}

            $follow->forceDelete();
            return $follow;
        }
    }

    public function toggleFollow($model = null)
    {
        return $this->isFollowable($model) ? $this->unFollowIt($model) : $this->followIt($model);
    }

    //兼容controller
    public function isFollow($type, $id)
    {
        return $this->hasMany(\App\Follow::class)
            ->where('followable_type', get_polymorph_types($type))
            ->where('followable_id', $id)
            ->count() ? true : false;
    }

}
