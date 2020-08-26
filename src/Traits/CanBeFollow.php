<?php


namespace Haxibiao\Sns\Traits;


use App\Follow;

trait CanBeFollow
{
    public static function bootCanBeFollow()
    {
        static::deleting(function($model) {
            $model->follows()->delete();
            $model->count_follows = 0;
            $model->save();
        });
    }

    //粉丝列表  作为CanBeFollow对象
    public function follows(): MorphMany
    {
        return $this->morphMany(Follow::class, 'followed');
    }


    public function getFollowedAttribute()
    {
        if ( checkUser() ) {
            return $this->isFollowed(getUser());
        }
        return false;
    }

    //是否已经被当前用户关注过了
    public function isFollowed($user = null)
    {
        return  (bool) $user->followers()
            ->where('user_id', '=', $user ? $user->id : getUser()->id)
            ->count();
    }

}