<?php

namespace Haxibiao\Sns\Traits;

use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Sns\Chat;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

trait ChatResolvers
{
    public function resolveCreateChat($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = getUser();
        $with = User::findOrFail($args['with_user_id']);
        $uids = [$with->id, $user->id];
        sort($uids);
        $uids = json_encode($uids);
        $chat = Chat::firstOrNew([
            'uids' => $uids,
        ]);
        $chat->save();

        $with->chats()->syncWithoutDetaching($chat->id);
        $user->chats()->syncWithoutDetaching($chat->id);
        return $chat;
    }

}
