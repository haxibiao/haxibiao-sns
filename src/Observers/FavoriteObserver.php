<?php

namespace Haxibiao\Sns\Observers;

use Haxibiao\Sns\Favorite;

class FavoriteObserver
{
    public function created(Favorite $favorite)
    {
        $this->countFavorites($favorite);
    }

    public function deleted(Favorite $favorite)
    {
        $this->countFavorites($favorite);
    }

    public function countFavorites(Favorite $favorite)
    {
        //更新被喜欢对象的计数（刷新时间，更新排序）
        if ($favorable = $favorite->likable) {
            if ($favorable->count_likes ?? null) {
                $favorable->count_favorites = $favorable->favorites->count();
                $favorable->save();
            }
        }
    }
}
