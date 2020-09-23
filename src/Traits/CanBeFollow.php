<?php


namespace Haxibiao\Sns\Traits;


use App\Follow;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
            return static::isFollowed($this);
        }
        return false;
    }

    //是否已经被当前用户关注过了
    public function isFollowed($model = null)
    {
        $methodName = config('haxibiao-sns.follow.passive.' . get_class($model));
        return  (bool) static::$methodName()
            ->where('user_id', '=',getUser()->id)
            ->count();
    }

}