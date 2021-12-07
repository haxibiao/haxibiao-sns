<?php

namespace Haxibiao\Sns\Traits;

use App\User;
use Haxibiao\Breeze\Exceptions\GQLException;
use Haxibiao\Breeze\Notifications\ChatJoinResultNotification;
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
    public static function store(array $uids, $subject = null, Int $status = Chat::PRIVATE_STATUS, Int $type = Chat::SINGLE_TYPE): Chat
    {
        // 给uids排重 排序 序列化 = 得到唯一性
        $uids     = array_unique($uids);
        $authUser = getUser();
        $authId   = $authUser->id;

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

        //虽然默认给了群聊类型，仔细判断一下属于的群聊类型
        // $type = count($uids) > 2 ? Chat::GROUP_TYPE : Chat::SINGLE_TYPE;

        //创建或返回存在的房间
        $chat = null;
        if ($type == Chat::SINGLE_TYPE && count($uids) == 2) {
            $chat = Chat::where('uids', $uidStr)->where('type', Chat::SINGLE_TYPE)->first();
            if (empty($chat)) {
                $chat = Chat::create([
                    'uids' => $uids,
                ]);
            }

        } else {
            $chat = Chat::create([
                'subject' => $subject,
                'status'  => $status,
                'uids'    => $uids,
                'user_id' => $authId, // 聊天发起人（群主）
                'type'    => $type,
                'number'  => now()->timestamp * random_int(1, 4),
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

    public static function addUserToChat($chat, $uids)
    {
        $newUids = array_merge(
            $chat->uids,
            $uids
        );
        $newUids = array_unique($newUids);
        if ($chat->privacy == Chat::BAN_PRIVACY) {
            throw new GQLException("该群聊不支持加群哦!");
        }

        if (count($newUids) > Chat::MAX_USERS_NUM) {
            throw new \Exception('邀请人数超过上限！');
        }
        sort($newUids);
        $chat->uids = $newUids;
        $chat->save();
    }

    //生成群头像
    public static function makeIcon($chat_id)
    {
        $chat = Chat::find($chat_id);
        if (count($chat->uids) >= 2) {
            //最多取前9个用户
            $users     = $chat->users()->take(9)->get();
            $pic_lists = [];
            foreach ($users as $user) {
                $pic_lists[] = $user->avatar;
            }
            if (count($pic_lists) >= 2) {
                $image_url = mergeImages($pic_lists);

                if ($image_url) {
                    $chat->update(['icon' => $image_url]);
                }
            }
        } else {
            $image_url = $chat->user->avatar;
            $chat->update(['icon' => $image_url]);
        }
    }

    public static function joinNotification($user, $chat, $result, $notification, $description)
    {
        //通过审核
        if ($result) {
            $uids = [$user->id];
            Chat::addUserToChat($chat, $uids);
        }
        $data               = $notification->data;
        $data['status']     = $result;
        $notification->data = $data;
        $notification->save();
        $user->notify(new ChatJoinResultNotification($chat, $result, $description));

    }
}