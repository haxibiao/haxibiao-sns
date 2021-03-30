<?php

namespace Haxibiao\Sns\Http\Api;

use App\Action;
use App\Category;
use App\Follow;
use App\Http\Controllers\Controller;
use App\User;
use Haxibiao\Breeze\Notifications\CategoryFollowed;
use Haxibiao\Breeze\Notifications\UserFollowed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FollowController extends Controller
{
    public function touch(Request $request, $id, $type)
    {
        $user   = $request->user();
        $result = 0;
        $follow = Follow::firstOrNew([
            'user_id'         => $user->id,
            'followable_id'   => $id,
            'followable_type' => get_polymorph_types($type),
        ]);
        if ($follow->id) {
            $follow->touch();
            return 1;
        }
        return 0;
    }

    public function toggle(Request $request, $id, $type)
    {
        $user   = $request->user();
        $result = 0;
        $follow = Follow::firstOrNew([
            'user_id'         => $user->id,
            'followable_id'   => $id,
            'followable_type' => get_polymorph_types($type),
        ]);
        if ($follow->id) {
            $follow->delete();
            //delete record action
            Action::where([
                'user_id'         => $user->id,
                'actionable_type' => 'follows',
                'actionable_id'   => $follow->id,
            ])->delete();
        } else {
            $follow->save();
            $result = 1;

            //record action
            $action = Action::updateOrCreate([
                'user_id'         => $user->id,
                'actionable_type' => 'follows',
                'actionable_id'   => $follow->id,
                'status'          => 1,
            ]);

            //notify when user follow
            if (get_polymorph_types($type) == 'users') {
                //避免短时间内重复提醒
                $cacheKey = 'user_' . $user->id . '_follow_' . $type . '_' . $id;
                if (!Cache::get($cacheKey)) {
                    $followable_user = $follow->followable;
                    $followable_user->notify(new UserFollowed($user));
                    $followable_user->forgetUnreads();
                    Cache::put($cacheKey, 1, 60);
                }
            }
            //notify when category follow
            if (get_polymorph_types($type) == 'categories') {
                //避免短时间内重复提醒
                $cacheKey = 'category_' . $user->id . '_follow_' . $type . '_' . $id;
                if (!Cache::get($cacheKey)) {
                    $followable_category = $follow->followable;
                    $followable_category->user->notify(new CategoryFollowed($followable_category, $user));
                    $followable_category->user->forgetUnreads();
                    Cache::put($cacheKey, 1, 60);
                }
            }
        }

        //更新被关注对象的被关注数
        $profile = $follow->followable->profile;
        $profile->count_follows = $follow->followable->follows()->count();
        $profile->save();

        //更新用户资料里的关注数
        $profile = $user->profile;
        $profile->count_followings = $user->followings()->count();
        $profile->save();

        return $result;
    }

    public function follows(Request $request)
    {
        $user    = $request->user();
        $follows = [];
        foreach ($user->followings as $item) {
            $follow['id']   = $item->followable->id;
            $follow['name'] = $item->followable->name;
            $follow['type'] = $item->followable_type;

            //用户才取avatar, 文集，专题都取logo
            $follow['img'] = $item->followable_type == 'users' ?
            $item->followable->avatarUrl : $item->followable->logoUrl;

            $updates           = $item->followable->articles()->where('articles.type', 'article')->where('articles.created_at', '>', $item->updated_at)->count();
            $follow['updates'] = $updates ? $updates : '';
            $follows[]         = $follow;
        }

        return $follows;
    }

    public function recommends(Request $request)
    {
        //TODO::  推荐应该排除已关注的那些
        $user               = $request->user();
        $data['user']       = $user;
        $data['recommends'] = [];

        $followable_users = $user->followings()
            ->where('followable_type', 'users')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();
        foreach ($followable_users as $follow) {
            $followable_user = $follow->followable;
            $followings      = $followable_user->followings()
                ->where('followable_type', '<>', 'collections')
                ->get();
            foreach ($followings as $follow) {
                $followable = $follow->followable;
                if ($followable) {
                    if ($follow->followable_type == 'users') {
                        $followable->collections = $followable->hasCollections()->take(2)->get();
                    }

                    $followable->is_followable      = $user->isFollow($follow->followable_type, $follow->followable_id);
                    $followable->type               = $follow->followable_type;
                    $followable->followable_user    = $followable_user->name;
                    $followable->followable_user_id = $followable_user->id;
                    $followable->fillForJs();
                    $data['recommends'][] = $followable;
                }
            }
        }

        $recommended_users = User::orderBy('id', 'desc')->paginate(10);
        foreach ($recommended_users as $recommended_user) {
            $recommended_user->followable  = $user->isFollow('users', $recommended_user->id);
            $recommended_user->collections = $recommended_user->hasCollections()->take(2)->get();
            $recommended_user->fillForJs();
        }
        $data['recommended_users'] = $recommended_users;

        $categories = Category::orderBy('id', 'desc')->paginate(10);
        foreach ($categories as $category) {
            $category->followable = $user->isFollow('categories', $category->id);
            $category->fillForJs();
        }
        $data['recommended_categories'] = $categories;

        return $data;
    }
}
