<?php

namespace Haxibiao\Sns\Traits;

use Haxibiao\Sns\Dislike;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Dislikeable
{
    /**
     * 用户的不感兴趣
     */
    public function dislikes(): HasMany
    {
        return $this->hasMany(Dislike::class);
    }
}
