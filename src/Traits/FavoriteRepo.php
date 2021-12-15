<?php

namespace Haxibiao\Sns\Traits;

use App\Visit;
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
        //普通movies是追剧，favorite_movies是收藏
        //之前就写成这样了，将错就错吧，这里注意区分就好
        if ($type == "favorite_movies") {
            $favorite = Favorite::firstOrNew([
                'user_id'        => getUserId(),
                'favorable_id'   => $id,
                'favorable_type' => 'movies',
                'tag'            => 'favorite',
            ]);

        } else {
            $favorite = Favorite::firstOrNew([
                'user_id'        => getUserId(),
                'favorable_id'   => $id,
                'favorable_type' => $type,
            ]);
        }

        if ($favorite->id) {
            $favorite->forceDelete();
            $favorite->favorited = false;
        } else {
            $favorite->save();
            $favorite->favorited = true;
        }

        if (currentUser()) {
            Visit::saveVisit(getUser(), $favorite, 'favorites');
        }
        return $favorite;
    }

    public static function getFavoritesQuery($favorable_type)
    {
        $user = getUser();
        $qb   = $user->hasFavorites();
        if ($favorable_type == "favorite_movies") {
            $qb = $qb->where('favorable_type', 'movies')
                ->where('tag', 'favorite')
                ->with('favorable')
                ->latest('id');

        } else {
            $qb = $qb->where('favorable_type', $favorable_type)
                ->where('tag', '!=', "favorite")
                ->with('favorable')
                ->latest('id');
        }
        return $qb;
    }
}