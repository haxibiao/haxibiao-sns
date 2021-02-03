<?php

namespace Haxibiao\Sns\Traits;

use App\User;
use Haxibiao\Sns\Favorite;
use Haxibiao\Sns\Follow;

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

    public function resolveToggleFavorited($root, $args, $context, $info)
    {
        //只能简单创建
        $user     = getUser();
        $favorite = \App\Favorite::firstOrNew([
            'user_id'    => $user->id,
            'favorable_id'   => data_get($args, 'id'),
            'favorable_type' => data_get($args, 'type'),
        ]);
        //取消收藏
        if ($favorite->id) {
            $favorite->delete();
            $favorite->favorited = false;
        } else {
            $favorite->save();
            $favorite->favorited = true;
            //检查收藏任务
            $user->reviewTasksByClass('Custom');
        }
        app_track_event('用户action', '收藏', '收藏_type:' . data_get($args, 'type'));
        return $favorite;
    }

    public function resolverMyFavorite($rootValue, array $args, $context, $resolveInfo)
    {
        $user            = User::find(data_get($args, 'user_id'));
        $favoriteBuilder = $user->favorites()->where('favorable_type', data_get($args, 'type') ?? 'movies')->groupBy('favorable_id')->orderBy('id', 'desc');
        app_track_event('用户', '用户收藏');
        return $favoriteBuilder;
    }

    public function toggleFavorite($rootValue, array $args, $context, $resolveInfo)
    {
        //只能简单创建
        $user     = getUser();
        $favorite = \App\Favorite::firstOrNew([
            'user_id'    => $user->id,
            'favorable_id'   => $args['article_id'],
            'favorable_type' => 'articles',
        ]);
        //取消收藏
        if ($favorite->id) {
            $favorite->delete();
        } else {
            $favorite->save();
        }

        return $favorite;
    }
}
