<?php

namespace Haxibiao\Sns\Traits;

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
}
