<?php

namespace Haxibiao\Sns\Traits;

use Haxibiao\Sns\Favorite;

trait FavoriteRepo
{
    /**
     * 切换收藏的状态
     * @param array $input
     * @return Favorite
     */
    public static function toggle($type, $id): Favorite
    {
        $user     = getUser();
        $favorite = Favorite::firstOrNew([
            'user_id'        => $user->id,
            'favorable_id'   => $id,
            'favorable_type' => $type,
        ]);

        if ($favorite->id) {
            $favorite->forceDelete();
            $favorite->favorited = false;
        } else {
            $favorite->save();
            $favorite->favorited = true;
        }

        return $favorite;
    }

    public static function getFavoritesQuery($favorable_type)
    {
        $user = getUser();
        $qb   = $user->hasFavorites()
            ->where('favorable_type', $favorable_type)
            ->with('favorable')
            ->latest('id');
        return $qb;
    }
}
