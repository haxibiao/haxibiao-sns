<?php

namespace Haxibiao\Sns\Traits;

use App\Image;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Breeze\Exceptions\GQLException;
use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Breeze\Notification;
use Haxibiao\Breeze\Notifications\ChatJoinNotification;
use Haxibiao\Sns\Chat;
use Haxibiao\Sns\ChatUser;
use Haxibiao\Sns\UserBlock;

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
        $type = data_get($args, 'type') ?? Chat::SINGLE_TYPE;
        //兼容答赚
        if (!$uids) {
            $uids = data_get($args, 'users');
        }

        //加上本人
        $uids = array_merge([$user->id], $uids);

        throw_if($user->chats()->count() > 4, GQLException::class, "最多只能创建4个群聊！");
        $nameExists = Chat::where("subject", $subject)->exists();
        throw_if($nameExists, GQLException::class, "群名已存在，请重新输入！");
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
        app_track_event("消息", "消息列表");
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
        $privacy = data_get($args, 'privacy', null);

        $isGroupOwner = $chat->user_id == $user->id;
        if (!$isGroupOwner) {
            throw new GQLException('权限不足！');
        }
        if ($subject) {
            $chat->subject = $subject;
        }
        if ($privacy) {
            $chat->privacy = $privacy;
        }
        if ($icon) {
            if (!blank($icon)) {
                $image = Image::saveImage($icon);
                if (!empty($image)) {
                    $chat->icon = $image->url;
                }
            }
        }
        if (!is_null($status)) {
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
        if (!in_array($chat->user_id, $newUids)) {
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
        $chatId = data_get($args, 'chat_id');
        $uids   = data_get($args, 'uids');
        $chat   = \App\Chat::findOrFail($chatId);

        Chat::addUserToChat($chat, $uids);

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
        app_track_event("消息", "删除群聊");
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

    //推荐群聊
    public function resolveRecommendChats($rootValue, $args, $context, $resolveInfo)
    {
        $user = getUser(false);
        return Chat::query()->when(!empty($user), function ($qb) use ($user) {
            $qb->where('user_id', "!=", $user->id);
        })->publishStatus()->orderBy('rank', 'desc');
    }

    //分享群聊
    public function resolveShareChat($rootValue, $args, $context, $resolveInfo)
    {
        $user      = getUser();
        $chat      = Chat::findOrFail($args['chat_id']);
        $user_code = enCode($user->id);
        $chat_code = enCode($chat->id);
        //分享码
        $shar_code = $user_code . $chat_code;
        // $domain    = array_random(config('cms.qrcode_traffic.redirect_urls')); //分享用四级域名
        $domain = app_domain();
        return "{$user->name}邀请你加入群聊-{$chat->name}\n【复制本条消息】分享码{$shar_code},打开剧好看加入群聊！😊😄😊😁🎉\n下载地址👉👉 {$domain}/app";
    }

    //通过分享码返回群聊
    public function resolveChatOfCode($rootValue, $args, $context, $resolveInfo)
    {
        $shar_code = $args['shar_code'];
        $shar_code = str_before(str_after($shar_code, "分享码"), ",");
        $num       = strlen($shar_code) / 2;
        $user_code = substr($shar_code, 0, $num);
        $chat_code = substr($shar_code, $num, $num);
        $user_id   = deCode($user_code);
        $chat_id   = deCode($chat_code);
        $chat      = Chat::find($chat_id);
        throw_if(empty($chat), GQLException::class, "群聊不存在或已解散!");
        $user = User::find($user_id);
        throw_if(empty($user), GQLException::class, "用户不存在或已注销!");
        return ["chat" => $chat, "user" => $user];
    }

    //搜索群聊
    public function resolveSearchChats($rootValue, $args, $context, $resolveInfo)
    {
        $keyword = $args['keyword'];
        app_track_event("用户操作", "搜索群聊", "搜索内容为: $keyword");
        return Chat::query()->where('status', Chat::PUBLIC_STATUS)->where(function ($qb) use ($keyword) {
            return $qb->where('subject', 'like', "%" . $keyword . "%")->orWhere('number', 'like', "%" . $keyword . "%");
        });
    }

    //申请加群
    public function resolveJoinChatApply($rootValue, $args, $context, $resolveInfo)
    {
        $description = $args['description'];
        $chat_id     = $args['chat_id'];
        $user        = getUser();

        $chat = Chat::findOrFail($chat_id);

        app_track_event("消息", "申请加群", "申请对象为: $user->id, 群聊id为: $chat_id");

        if (in_array($user->id, $chat->uids)) {
            throw new UserException("您已经是该群聊的成员了!");
        }
        if ($chat->privacy == Chat::BAN_PRIVACY) {
            throw new UserException("该群聊不支持加群哦!");
        }

        //公开状态直接通过
        if ($chat->privacy == Chat::WITHOUT_CHECK_PRIVACY) {
            $uids = [$user->id];
            Chat::addUserToChat($chat, $uids);
        } else {
            //被拉黑后不发送加群申请给群主
            $userBlock = UserBlock::where('user_id', $chat->user->id)->where('blockable_type', 'users')->where('blockable_id', $user->id)->first();
            //私密状态要通知群主审核
            if (empty($userBlock)) {
                $chat->user->notify(new ChatJoinNotification($user, $chat, $description));
            }
        }
        return $chat;
    }

    //审核群聊申请
    public function resolveJoinChatCheck($rootValue, $args, $context, $resolveInfo)
    {
        $chat_id         = $args['chat_id'];
        $is_block        = $args['is_block'] ?? false;
        $notification_id = $args['notification_id']; //处理通知
        $result          = $args['result']; //审核结果true false
        $description     = $args['description'] ?? null; //审核说明
        $chat            = Chat::findOrFail($chat_id);

        $notification = Notification::find($notification_id);
        //审核人
        $user = getUser();
        //申请群聊加群用户
        $notification_user = User::findOrFail($notification->user->id);
        //拉黑用户
        if ($is_block) {
            UserBlock::create([
                'user_id'        => $user->id,
                'blockable_id'   => $notification_user->id,
                'blockable_type' => "users",
            ]);
        }
        if ($notification) {
            Chat::joinNotification($notification_user, $chat, $result, $notification, $description);
        }
        return $chat;
    }

}
