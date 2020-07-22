<?php

namespace Haxibiao\Sns\Traits;

trait FollowAttrs
{
    public function getFollowUserAttribute()
    {
        return $this->followed;
    }
}
