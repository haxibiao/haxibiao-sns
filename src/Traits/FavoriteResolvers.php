<?php

namespace Haxibiao\Sns\Traits;


use Haxibiao\Sns\Favorite;

trait FavoriteResolvers
{
    public function resolveFavorites($root, $args, $context, $info)
    {
        app_track_event('个人中心', '我的收藏');
        return Favorite::getFavoritesQuery($args['type']);
    }

    public function resolveToggleFavorite($root, $args, $context, $info)
    {
        app_track_event("收藏", $args['type'], $args['id']);
        return Favorite::toggle($args['type'], $args['id']);
    }
}
