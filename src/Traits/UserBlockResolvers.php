<?php
namespace Haxibiao\Sns\Traits;

use App\Post;
use App\User;
use Haxibiao\Sns\UserBlock;
use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Breeze\Exceptions\GQLException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

trait UserBlockResolvers
{

    //TODO::屏蔽用户、动态、等等都只要一个接口，逻辑都一样的

    //添加用户黑名单（屏蔽用户）
    public function addUserBlock($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        app_track_event("用户操作","拉黑用户");
        if ($user = currentUser()) {
            $block_object = User::find($args['id']);
            throw_if(empty($block_object), GQLException::class, "屏蔽失败，不存在该用户");

            //跳过已经屏蔽过的用户
            $existUser = UserBlock::where("user_id", $user->id)
                ->where("blockable_id", $args['id'])
                ->where('blockable_type', 'users')
                ->first();

            throw_if(!empty($existUser), GQLException::class, "屏蔽失败，您已屏蔽过该用户");

            return UserBlock::create([
                'user_id'        => $user->id,
                'blockable_id'   => $block_object->id,
                'blockable_type' => "users",
            ]);
        }
    }

    /**
     * 对动态的不感兴趣
     */
    public function addArticleBlock($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if ($user = currentUser()) {
            $block_object = Post::find($args['id']);
            throw_if(empty($block_object), GQLException::class, "屏蔽失败，不存在该动态");

            //跳过已经屏蔽过的
            $existUser = UserBlock::where("user_id", $user->id)
                ->where("blockable_id", $args['id'])
                ->where('blockable_type', 'posts')
                ->first();

            throw_if(!empty($existUser), GQLException::class, '添加\'不感兴趣\'失败，您已经对该动态标记过\'不感兴趣\'');

            return UserBlock::create([
                'user_id'        => $user->id,
                'blockable_id'   => $block_object->id,
                'blockable_type' => "posts",
            ]);

        }
    }

    /**
     * 用户‘我的黑名单’列表
     */
    public function resolveUserBlocks($rootValue, array $args, $context, $resolveInfo)
    {
        $blockUserIds = UserBlock::where("user_id", $args['user_id'])
            ->where('blockable_type', 'users')->pluck('blockable_id');
        return User::whereIn('id', $blockUserIds) ?? null;
    }

    //移除‘我的黑名单’用户
    public function removeUserBlock($rootValue, array $args, $context, $resolveInfo)
    {
        $user = getUser();
        return UserBlock::where("user_id", $user->id)
            ->where("blockable_id", $args['id'])
            ->where('blockable_type', 'users')
            ->delete();
    }
}
