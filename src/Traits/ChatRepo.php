<?php

namespace Haxibiao\Sns\Traits;

use App\User;
use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Sns\Chat;

trait ChatRepo
{
    /**
     * 根据聊天群里的人，创建并返回聊天房间
     *
     * @param array $uids 聊天的人的ids
     * @return Chat
     */
    public static function store(array $uids): Chat
    {
        //给uids排重 排序 序列化 = 得到唯一性
        $uids = array_unique($uids);
        if (count($uids) < Chat::MIN_USERS_NUM) {
            throw new UserException('私信失败,请稍后再试!');
        }
        sort($uids);
        $uidStr = json_encode($uids);

        //创建或返回存在的房间
        $chat = Chat::firstOrNew([
            'uids' => $uidStr,
        ]);
        if (!$chat->id) {
            $chat = Chat::create(['uids' => $uids]);
        }

        //进入私聊，意图聊天的时间？
        $chat->touch();

        //observer 同步 群内用户和聊天的多对多关系 变化
        return $chat;
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
