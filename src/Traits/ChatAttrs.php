<?php

namespace Haxibiao\Sns\Traits;

use App\Notification;
use App\User;

trait ChatAttrs
{

    /**
     * 临时兼容icon
     *
     * @return string
     */
    public function getIconAttribute()
    {
        if (blank($this->getRawOriginal('icon'))) {
            $user = currentUser();
            if (empty($user)) {
                //给个默认的icon
                $user = User::first();
            }
            return $user->avatar_url;
        }
        return $this->getRawOriginal('icon');
    }

    /**
     * 获取聊天成员
     *
     * @param integer $offset
     * @param integer $limit
     * @return void
     */
    public function getMembersAttribute($offset = 0, $limit = 10)
    {
        $uids = is_array($this->uids) ? $this->uids : @json_decode($this->uids) ?? [];
        return User::whereIn('id', $uids)->skip($offset)->take($limit)->get();
    }

    public function getUnreadsCountAttribute()
    {
        $user = getUser();

        return $user->chatUnreads($this->id);
    }

    public function getUnreadsAttribute()
    {
        return $this->pivot->unreads ?? 0;
    }

    public function getWithUserAttribute()
    {
        if ($user = currentUser()) {
            $uids        = is_array($this->uids) ? $this->uids : @json_decode($this->uids) ?? [];
            $current_uid = $user->id;
            $with_id     = array_sum($uids) - $current_uid;
            $with        = User::find($with_id);
        }
        //确保聊天对象不为空，有问题的时候，消息发送给user id 1
        return $with ?? User::find(1);
    }

    public function getLastMessageAttribute()
    {
        $message = $this->messages()->latest('id')->first();
        return $message ? $message : null;
    }

    //FIXME: 这应该一个已读全部未读私信的mutation
    public function getClearUnreadAttribute()
    {
        if ($user = currentUser()) {
            $unread_notifications = Notification::where([
                'type'          => 'Haxibiao\Breeze\Notifications\ChatNewMessage',
                'notifiable_id' => $user->id,
                'read_at'       => null,
            ])->get();
            foreach ($unread_notifications as $notify) {
                $notify->read_at = now();
                $notify->save();
            }
            return true;
        }
        return false;
    }

}
