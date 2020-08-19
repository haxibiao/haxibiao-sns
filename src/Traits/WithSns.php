<?php

namespace Haxibiao\Sns\Traits;

use App\Comment;
use App\Follow;

trait WithSns
{
    public function likedTableIds($likavleType, $likableIds)
    {
        return $this->likes()->select('likable_id')
            ->whereIn('likable_id', $likableIds)
            ->where('likable_type', $likavleType)
            ->get()
            ->pluck('likable_id');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function followers()
    {
        return $this->morphMany(Follow::class, 'followed');
    }
}
