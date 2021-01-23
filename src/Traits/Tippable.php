<?php

namespace Haxibiao\Sns\Traits;

trait Tippable
{
    /**
     * 用户的打赏
     */
    public function hasTips()
    {
        return $this->hasMany(Tip::class);
    }

    /**
     * 内容的被打赏
     */
    public function tips()
    {
        return $this->morphMany(Tip::class, 'tipable');
    }

}
