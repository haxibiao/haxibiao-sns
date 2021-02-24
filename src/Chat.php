<?php

namespace Haxibiao\Sns;

use Haxibiao\Sns\ChatUser;
use App\Notification;
use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Breeze\Model;
use Haxibiao\Breeze\Traits\HasFactory;
use Haxibiao\Breeze\User;
use Haxibiao\Sns\Traits\ChatAttrs;
use Haxibiao\Sns\Traits\ChatResolvers;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Chat extends Model
{
    use HasFactory;
    use ChatAttrs;
    use ChatResolvers;

    protected $guarded = [];

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
        $user    = getUser();
        $chat_id = $args['chat_id'];
        $chat    = \App\Chat::findOrFail($chat_id);
        //未读消息数归0
        ChatUser::where('chat_id', $chat->id)
            ->where('user_id', '=', $user->id)
            ->update(['unreads' => 0]);
        Notification::where('notifiable_type', 'users')
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->where('type', 'App\Notifications\ChatNewMessage')
            ->where('data->chat_id', $chat->id)
            ->get()
            ->markAsRead();

        return $chat->messages()->latest('id');
    }
}
