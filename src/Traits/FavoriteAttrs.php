<?php

namespace Haxibiao\Sns\Traits;

trait FavoriteAttrs
{
    public function getCreatedAtMsgAttribute()
    {
        return time_ago($this->created_at);
    }

    public function getQuestionAttribute()
    {
        return $this->favorable;
    }

    public function getPostAttribute()
    {
        return $this->favorable;
    }
}
