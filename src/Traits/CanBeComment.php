<?php

namespace Haxibiao\Sns\Traits;


use App\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait CanBeComment
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

}