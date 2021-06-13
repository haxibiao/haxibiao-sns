<?php

namespace Haxibiao\Sns\Http\Api;

use App\Http\Controllers\Controller;
use Haxibiao\Sns\Like;
use Haxibiao\Sns\Traits\LikeRepo;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * 用户点赞/取消点赞
     */
    public static function toggle(Request $request, $id, $type)
    {
        $like = new Like();
        $user = $request->user();
        $data = [
            'user_id'      => $user->id,
            'likable_id'   => $id,
            'likable_type' => str_plural($type),
        ];
        LikeRepo::toggle($user, $data['likable_type'], $id);
        return $like->likeUsers($data);
    }

    public function getForGuest(Request $request, $id, $type)
    {
        return $this->get($request, $id, $type);
    }

    public function get(Request $request, $id, $type)
    {
        $like = new Like();
        $data = [
            'likable_id'   => $id,
            'likable_type' => str_plural($type),
        ];
        return $like->likeUsers($data);
    }
}
