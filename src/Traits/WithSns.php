<?php

namespace Haxibiao\Sns\Traits;

use App\Comment;
use App\Follow;

/**
 * 内容的Sns特性
 */
trait WithSns
{
    use Likeable;

    public function likedTableIds($likavleType, $likableIds)
    {
        return $this->likes()->select('likable_id')
            ->whereIn('likable_id', $likableIds)
            ->where('likable_type', $likavleType)
            ->get()
            ->pluck('likable_id');
    }

    //commentable
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    //FIXME: 整理到用户UseSns
    public function followers()
    {
        return $this->morphMany(Follow::class, 'followed');
    }
}
