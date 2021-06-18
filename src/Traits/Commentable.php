<?php

namespace Haxibiao\Sns\Traits;

use Haxibiao\Sns\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Commentable
{
    public function hasComments()
    {
        return $this->hasMany(Comment::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

}
