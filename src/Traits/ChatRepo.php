<?php

namespace Haxibiao\Sns\Traits;

use App\User;
use Haxibiao\Sns\Chat;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

trait ChatRepo
{
    /**
     * 根据聊天群里的人，创建并返回聊天房间
     *
     * @param array $uids 聊天的人的ids
     * @return Chat
     */
    public static function store(array $uids, $subject = null, Int $status = Chat::PRIVATE_STATUS, Int $type = Chat::GROUP_TYPE): Chat
    {
        // 给uids排重 排序 序列化 = 得到唯一性
        $uids   = array_unique($uids);
        $authId = data_get(getUser(false), 'id');

        // 群组人数上限,保留优先选择的用户
        if (count($uids) > Chat::MAX_USERS_NUM) {
            $uids = array_filter($uids, function ($uid) use ($authId) {
                return $uid != $authId;
            });
            $uids = array_slice($uids, 0, Chat::MAX_USERS_NUM - 1);
            $uids = array_merge($uids, Arr::wrap($authId));
        }

        //允许创建一个人的群聊
        // if (count($uids) < Chat::MIN_USERS_NUM) {
        //     throw new UserException('私信失败,请稍后再试!');
        // }
        sort($uids);
        $uidStr = json_encode($uids);

        if (empty($type)) {
            $type = count($uids) > 2 ? Chat::GROUP_TYPE : Chat::SINGLE_TYPE;
        }

        //创建或返回存在的房间
        $chat = Chat::firstOrNew([
            'uids' => $uidStr,
        ]);
        if (!$chat->id) {
            $chat = Chat::create([
                'subject' => $subject,
                'status'  => $status,
                'uids'    => $uids,
                'user_id' => $authId, // 聊天发起人（群主）
                'type' => $type,
            ]);
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

    public function saveDownloadImage($file)
    {
        if ($file) {
            $cover   = '/chat' . $this->id . '_' . time() . '.png';
            $cosDisk = Storage::cloud();
            $cosDisk->put($cover, \file_get_contents($file->path()));

            return cdnurl($cover);
        }
    }
}
