<?php

namespace Haxibiao\Sns\Traits;


use App\Follow;

/**
 * Trait CanFollow
 * @package Haxibiao\Sns\Traits
 * 现在的项目中因为只有user 拥有Follow其他事物的能力，所以 CanFollow只会在User中被引用
 * user可以进行follow操作，同时也是被follow的对象
 */
trait CanFollow
{
    /**
     * @return mixed
     * 1. 粉丝列表：user 作为CanBeFollow对象
     * 2. 维护命名冲突而冗余的关系(follows冲突)
     * 3. 在其他的CanBeFollow对象中（如Category，粉丝列表的关系名为 follows
     */
    public function followers()
    {
        return $this->morphMany(Follow::class, 'followed');
    }

    /**
     * 关注列表： user 作为CanFollow对象
     * 兼容答赚
     */
    public function follows()
    {
        return $this->hasMany(Follow::class);
    }

    /**
     * @return mixed
     * 关注列表，兼容工厂系
     */
    public function followings()
    {
        return $this->hasMany(Follow::class);
    }
    //关注的集合
    public function followCollections()
    {
        return $this->hasMany(Follow::class)
            ->where('followed_type','collections');
    }

    //关注的分类
    public function followCategories()
    {
        return $this->hasMany(Follow::class)
            ->where('followed_type','categories');
    }
    //关注当前model
    public function followIt($model = null)
    {
        $methodName = config('haxibiao-sns.follow.passive.' . get_class($model));
        app_track_event('用户',"关注");
        if(checkUser()) {
            $user = getUser();
            $follow = $model->$methodName()
                ->where('user_id', '=', $user->id)
                ->first();
            if($follow) {
                return;
            }

            $follow = new Follow();
            $follow->user_id = $user->id;
            $save = $model->$methodName()->save($follow);
            return $save;
        }
    }

    public function unFollowIt($model = null)
    {
        $methodName = config('haxibiao-sns.follow.passive.' . get_class($model));
        if(checkUser()) {
            $user = getUser();
            $follow = $model->$methodName()
                ->where('user_id', '=', $user->id)
                ->first();
            if(!$follow) { return; }

            $follow->forceDelete();
            return $follow;
        }
    }

    public function toggleFollow($model = null)
    {
        return $this->isFollow($model) ? $this->unFollowIt($model) : $this->followIt($model);
    }

    //是否已经关注过当前model
    public function isFollow($model = null)
    {
        $methodName = config('haxibiao-sns.follow.passive.' . get_class($model));
        $count = (bool)$model->$methodName()
            ->where('user_id', getUser()->id)
            ->count();
        return $count;
    }



}