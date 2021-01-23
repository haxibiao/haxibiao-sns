<?php

namespace Haxibiao\Sns\Traits;

trait Favorable
{
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedArticles()
    {
        return $this->hasMany(Favorite::class)->where('faved_type', 'articles');
    }
}
