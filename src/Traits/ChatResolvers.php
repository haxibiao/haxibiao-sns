<?php

namespace Haxibiao\Sns\Traits;

use App\Image;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Breeze\Exceptions\GQLException;
use Haxibiao\Breeze\Notification;
use Haxibiao\Sns\Chat;
use Haxibiao\Sns\ChatUser;

trait ChatResolvers
{

    public function resolveCreateChat($root, $args, $context, ResolveInfo $info)
    {
        app_track_event("消息", "创建聊天");
        $user = getUser();

        //聊天参与的人uids
        $uids = data_get($args, 'uids');
        //群聊昵称
        $subject = data_get($args, 'subject');
        //群聊状态（1公开0私聊）
        $status = data_get($args, 'status', Chat::PRIVATE_STATUS);
        //群聊类型（少于1人也可以指定为群聊）
        $type = data_get($args, 'type') ?? Chat::GROUP_TYPE;
        //兼容答赚
        if (!$uids) {
            $uids = data_get($args, 'users');
        }

        //加上本人
        $uids = array_merge([$user->id], $uids);

        //创建聊天群
        return Chat::store($uids, $subject, $status, $type);
    }

    public function resolveChat($root, $args, $context, ResolveInfo $info)
    {
        app_track_event("消息", '私聊ID', $args['chat_id']);
        return Chat::getChat($args['chat_id']);
    }

    public function resolveChats($root, $args, $context, ResolveInfo $info)
    {
        app_track_event("消息", "获取私信列表");

        //尊重参数user_id查询调试用户消息列表
        if ($user_id = data_get($args, 'user_id')) {
            $user = User::find($user_id);
        }

        if (!isset($user)) {
            $user = getUser();
        }

        return $user->chats()->latest('updated_at');
    }

    public function resolveUserChats($rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {

        if ($user_id = data_get($args, 'user_id', null)) {
            $user = User::find($user_id);
        } else {
            $user = getUser();
        }
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

    public function resolveUpdateChat($rootValue, $args, $context, $resolveInfo)
    {
        $user   = getUser();
        $chatId = data_get($args, 'chat_id');

        $chat    = \App\Chat::findOrFail($chatId);
        $subject = data_get($args, 'subject', null);
        $icon    = data_get($args, 'icon', null);
        $status  = data_get($args, 'status', null);

        $isGroupOwner = $chat->user_id == $user->id;
        if (!$isGroupOwner) {
            throw new GQLException('权限不足！');
        }
        if ($subject) {
            $chat->subject = $subject;
        }
        if ($icon) {
            if (!blank($icon)) {
                $image = Image::saveImage($icon);
                if (!empty($image)) {
                    $chat->icon = $image->url;
                }
            }
        }
        if ($status) {
            $chat->status = $status;
        }
        $chat->save();
        return $chat;
    }

    public function resolveRemoveParticipantsInGroupChat($rootValue, $args, $context, $resolveInfo)
    {
        $user   = getUser();
        $chatId = data_get($args, 'chat_id');
        $uids   = data_get($args, 'uids');
        $chat   = \App\Chat::findOrFail($chatId);

        $isGroupOwner = $chat->user_id == $user->id;
        if (!$isGroupOwner) {
            throw new GQLException('权限不足！');
        }

        $newUids = array_diff(
            $chat->uids,
            $uids
        );
        $newUids = array_merge([$user->id], $newUids);
        $newUids = array_unique($newUids);

        // 解散聊天室
        if (count($newUids) < Chat::MIN_USERS_NUM) {
            $chat->delete();
            return $chat;
        }
        sort($newUids);
        $chat->uids = $newUids;
        $chat->save();

        return $chat;
    }

    public function resolveAddParticipantsInGroupChat($rootValue, $args, $context, $resolveInfo)
    {
        $user   = getUser();
        $chatId = data_get($args, 'chat_id');
        $uids   = data_get($args, 'uids');
        $chat   = \App\Chat::findOrFail($chatId);

        $newUids = array_merge(
            $chat->uids,
            $uids
        );
        $newUids = array_merge([$user->id], $newUids);
        $newUids = array_unique($newUids);

        if (count($newUids) > Chat::MAX_USERS_NUM) {
            throw new \Exception('邀请人数超过上限！');
        }
        sort($newUids);
        $chat->uids = $newUids;
        $chat->save();

        return $chat;
    }

    public function resolveSearchParticipantsInGroupChat($rootValue, $args, $context, $resolveInfo)
    {
        $keyword = data_get($args, 'keyword');
        $chatId  = data_get($args, 'chat_id');
        $chat    = \App\Chat::findOrFail($chatId);
        return $chat->users()->where('name', 'like', "%$keyword%");
    }

    public function resolveDeleteChat($rootValue, $args, $context, $resolveInfo)
    {
        $user   = getUser();
        $chatId = data_get($args, 'chat_id');
        $chat   = \App\Chat::findOrFail($chatId);
        $userId = $chat->user_id;

        // 如果是群主，解散群聊
        if ($userId === $user->id) {
            $chat->delete();
            return $chat;
        }

        $chat->uids = array_filter($chat->uids, function ($uid) use ($user) {
            return $user->id != $uid;
        });
        $chat->save();
        return $chat;
    }

}
