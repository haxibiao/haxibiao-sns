<?php

namespace Haxibiao\Sns\Traits;

use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

trait LikeResolvers
{

    public function resolveCreate($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $likedId   = data_get($args, 'liked_id');
        $likedType = data_get($args, 'liked_type');

        $modelString = Relation::getMorphedModel($likedType);
        $model       = $modelString::findOrFail($likedId);
        return $model->likeIt();
    }

    //resolvers
    public function resolveLikes($root, $args, $context, $info)
    {
		$type = data_get($args,'liked_type',data_get($args,'type'));
		if($type == 'articles'){
			$type = 'posts';
		}
        $user_id = $args['user_id'];
        if ($user = User::find($user_id)) {
            if (isset($args['type'])) {
                return $user->likes()
                    ->where('likable_type', $type)
                    ->latest('id')
                    ->with('likable');
            }
            return $user->likes()->latest('id')->with('likable');
        }
    }

    public function resolveToggleLike($root, $args, $context, $info)
    {
        $user = getUser();
        //印象视频等一批App用的都是liked_type和liked_id，兼容一下
        return static::toggle($user, $args['type'] ?? $args['liked_type'], $args['id'] ?? $args['liked_id']);
    }
}
