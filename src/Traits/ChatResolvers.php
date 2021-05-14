<?php

namespace Haxibiao\Sns\Traits;

use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Breeze\Notification;
use Haxibiao\Sns\Chat;
use Haxibiao\Sns\ChatUser;

trait ChatResolvers
{

    public function resolveCreateChat($root, $args, $context, ResolveInfo $info)
    {
        app_track_event("消息", "创建聊天");

        $uids = [];
        //兼容答赚
        if ($args['with_user_id'] ?? null) {
            $user = getUser();
            $with = User::findOrFail($args['with_user_id']);
            $uids = [$with->id, $user->id];
        } else if ($args['users'] ?? null) {
            $uids = $args['users'];
        }

        return Chat::store($uids);
    }

    public function resolveChat($root, $args, $context, ResolveInfo $info)
    {
        app_track_event("消息", '私聊ID', $args['chat_id']);
        return Chat::getChat($args['chat_id']);
    }

    public function resolveChats($root, $args, $context, ResolveInfo $info)
    {
        app_track_event("消息", "获取私信列表");
        $user = getUser();
        return $user->chats()->latest('updated_at');
    }

    public function resolveUserChats($rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $user = $args['user_id'] ? User::find($args['user_id']) : getUser();
        $user = isset($user) ? $user : getUser();
        if ($user) {
            return $user->chats();
        }
    }

    public function resolveMessages($rootValue, array $args, $context, ResolveInfo $resolveInfo)
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
            ->where('type', 'Haxibiao\Breeze\Notifications\ChatNewMessage')
            ->where('data->chat_id', $chat->id)
            ->get()
            ->markAsRead();

        return $chat->messages()->latest('id');
    }

}
