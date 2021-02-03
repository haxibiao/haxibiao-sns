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

    /**
     * @deprecated 兼容旧属性
     */
    public function notLikes(){
        return $this->hasMany(Dislike::class);
    }
}
