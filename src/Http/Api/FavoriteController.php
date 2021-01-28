<?php

namespace Haxibiao\Sns\Http\Api;

use App\Action;
use App\Favorite;
use App\Http\Controllers\Controller;
use Haxibiao\Breeze\Notifications\ArticleFavorited;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, $id, $type)
    {
        $user     = $request->user();
        $result   = 0;
        $favorite = Favorite::firstOrNew([
            'user_id'        => $user->id,
            'favorable_id'   => $id,
            'favorable_type' => get_polymorph_types($type),
        ]);
        if ($favorite->id) {
            $favorite->delete();
            Action::where([
                'user_id'         => $user->id,
                'actionable_type' => 'favorites',
                'actionable_id'   => $favorite->id,
            ])->delete();
        } else {
            $favorite->save();
            $result = 1;
            //record action
            $action = Action::updateOrCreate([
                'user_id'         => $user->id,
                'actionable_type' => 'favorites',
                'actionable_id'   => $favorite->id,
            ]);

            //发送通知
            $article = $favorite->faved;
            $article->user->notify(new ArticleFavorited($article, $user));
        }

        $user->count_favorites = $user->hasFavorites()->count();
        $user->save();

        return $result;
    }

    public function get(Request $request, $id, $type)
    {
        $favorite = Favorite::firstOrNew([
            'user_id'        => $request->user()->id,
            'favorable_id'   => $id,
            'favorable_type' => $type,
        ]);
        return $favorite->id;
    }
}
