<?php

namespace Haxibiao\Sns\Traits;

use App\Follow;

/**
 * 用户的Sns特性
 */
trait UseSns
{
    /**
     * 粉丝
     */
    public function followers()
    {
        return $this->morphMany(Follow::class, 'followed');
    }
}
