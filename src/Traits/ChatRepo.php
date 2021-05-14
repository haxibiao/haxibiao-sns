<?php

namespace Haxibiao\Sns\Traits;

use App\User;
use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Sns\Chat;

trait ChatRepo
{
    public static function store(array $users): Chat
    {
        //给ID排序
        $users = array_unique($users);
        if (count($users) < Chat::MIN_USERS_NUM) {
            throw new UserException('私信失败,请稍后再试!');
        }

        //是否存在房间
        $chat = Chat::where('type', Chat::SINGLE_TYPE)
            ->where('uids', json_encode($users))
            ->first();
        if (!is_null($chat)) {
            return $chat;
        }

        //observer在后面处理
        return Chat::create(['uids' => $users]);
    }

    public static function getChat($chatId)
    {
        return Chat::find($chatId);
    }

    public static function getChats(User $user, $limit = 10, $offset = 0)
    {
        return $user->chats()->latest('updated_at')->take($limit)->skip($offset)->get();
    }
}
