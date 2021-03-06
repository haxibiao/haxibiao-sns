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

    //FIXME: 这个语义也要重构
    public function getFollowableAttribute()
    {
        if (currentUser()) {
            return static::isFollowable($this);
        }
        return false;
    }

    //是否已经关注过当前model (应该语义上是  isFollowed)
    public function isFollowable($model = null)
    {
        //性能优化: 仅查询详情页sns状态信息时执行
        if (request('fetch_sns_detail')) {
            $exists = (bool) $model->followers()
                ->where('user_id', getUserId())
                ->exists();
            return $exists;
        }
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

    //兼容controller
    public function isFollow($type, $id)
    {
        return $this->hasMany(\App\Follow::class)
            ->where('followable_type', get_polymorph_types($type))
            ->where('followable_id', $id)
            ->exists() ? true : false;
    }

    //内容被关注状态,如是否已关注某合集
    public function getFollowedAttribute()
    {
        //性能优化: 仅查询详情页sns状态信息时执行
        if (request('fetch_sns_detail')) {
            if (currentUser()) {
                return $this->followers()
                    ->where('user_id', getUserId())
                    ->exists();
            }
        }
        return false;
    }
}
