<?php

namespace Haxibiao\Sns\Traits;

use App\User;
use Haxibiao\Sns\Favorite;

trait FavoriteResolvers
{

    /**
     * 我的收藏（含追剧）
     */
    public function resolveFavorites($root, $args, $context, $info)
    {
        request()->request->add(['fetch_sns_detail' => true]);
        app_track_event('个人中心', '我的收藏');
        return Favorite::getFavoritesQuery($args['type']);
    }

    /**
     * 用户的收藏 (TA在追的剧)
     */
    public function resolveUserFavorites($rootValue, array $args, $context, $resolveInfo)
    {
        request()->request->add(['fetch_sns_detail' => true]);
        $user_id         = data_get($args, 'user_id');
        $type            = data_get($args, 'type') ?? 'movies';
        $favoriteBuilder = Favorite::where('user_id',$user_id)->where('favorable_type', $type)->orderBy('id', 'desc');
        app_track_event('用户', '用户收藏');
        return $favoriteBuilder;
    }

    /**
     * 印象视频前端最新的收藏接口
     */
    public function resolveToggleFavorite($root, $args, $context, $info)
    {
        request()->request->add(['fetch_sns_detail' => true]);
        $id   = data_get($args, 'id');
        $type = data_get($args, 'type');
        app_track_event("收藏", $type, $id);
        return Favorite::toggle($type, $id);
    }

    /**
     * ivan:据说印象视频和旧gqls用这个接口，但是最新印视频前端看起用的是 resolveToggleFavorite
     */
    public function resolveToggleFavorited($root, $args, $context, $info)
    {
        request()->request->add(['fetch_sns_detail' => true]);
        $id   = data_get($args, 'id');
        $type = data_get($args, 'type');

        $favorite = Favorite::toggle($type, $id);
        // app_track_event("收藏", $type, $id);

        //印象视频差异部分 ---- start
        if ($favorite->favorited) {
            //检查收藏任务
            getUser()->reviewTasksByClass('Custom');
        }
        app_track_event('用户action', '收藏', '收藏_type:' . $type);
        //印象视频差异部分 ---- end

        return $favorite;
    }

    public function toggleFavorite($rootValue, array $args, $context, $resolveInfo)
    {
        request()->request->add(['fetch_sns_detail' => true]);
        //只能简单创建
        $user     = getUser();
        $favorite = \App\Favorite::firstOrNew([
            'user_id'        => $user->id,
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
