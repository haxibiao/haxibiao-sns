<?php

namespace Haxibiao\Sns;

use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Breeze\Model;
use Haxibiao\Breeze\User;
use Haxibiao\Sns\Traits\ChatAttrs;
use Haxibiao\Sns\Traits\ChatResolvers;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Chat extends Model
{
    use ChatAttrs;
    use ChatResolvers;

    public $fillable = [
        'uids',
        'last_message_id',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('unreads');
    }

    //resolvers
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

    public function resolveUserChats($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = $args['user_id'] ? User::find($args['user_id']) : getUser();
        if ($user) {
            return $user->chats();
        }
        return null;
    }

    public function resolveMessages($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $chat = Chat::findOrFail($args['chat_id']);
        return $chat->messages()->latest('id');
    }
}
