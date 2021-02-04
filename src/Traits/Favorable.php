<?php

namespace Haxibiao\Sns\Traits;

use Haxibiao\Sns\Favorite;

trait Favorable
{
    /**
     * 内容是否已被当前用户收藏
     */
    public function getIsFavoritedAttribute()
    {
        //FIXME: 收藏记录数据量50w+之前记得检查index(2 morh columns + user_id column)
        if ($user = getUser(false)) {
            return $this->favorites()->where('user_id', $user->id)->exists();
        }
        return false;
    }

    /**
     * 用户的收藏
     */
    public function hasFavorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * 用户收藏的文章
     */
    public function favoritedArticles()
    {
        return $this->hasMany(Favorite::class)->where('favorable_type', 'articles');
    }

    public function favoritedMovie()
    {
        return $this->hasMany(\App\Favorite::class)->where('favorable_type', 'movies');
    }

    /**
     * 内容的被收藏列表
     */
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favorable');
    }

    public function favorable()
    {
        return $this->morphTo();
    }

}
