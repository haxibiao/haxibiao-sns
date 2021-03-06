<?php

namespace Haxibiao\Sns\Traits;

use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Sns\Notice;

trait NoticeRepo
{
    public static function addNotice(array $data)
    {
        $title      = \data_get($data, 'title');
        $content    = \data_get($data, 'content');
        $to_user_id = \data_get($data, 'to_user_id');
        $type       = \data_get($data, 'type');
        return Notice::create(
            [
                'title'      => $title,
                'content'    => $content,
                'to_user_id' => $to_user_id,
                'user_id'    => 1,
                'type'       => $type,
            ]
        );
    }

    /**
     * 获取官方公告
     * @param int $limit
     * @param int $offset
     * @param null $user
     * @return array
     */
    public static function getNotices($limit = 10, $offset = 0, $user = null)
    {
        $notices = [];

        $notices = Notice::getNoticesQuery()
            ->take($limit)
            ->skip($offset)
            ->get();
        if ($user) {
            Notice::markAsRead($user, $notices);
        }

        return $notices;
    }

    public static function markAsRead($user, $notices)
    {
        $user->readNotices()->syncWithoutDetaching($notices->pluck('id'));
        $notices->each(function ($notice) {
            $notice->touch(); //标记最近更新时间是1小时内的，算已读
            $notice->is_read = true;
        });
    }

    public static function getNoticesQuery()
    {
        return Notice::where('expires_at', '>', now())->latest('id');
    }

    /**
     * 获取公告详情
     *
     * @param int $id
     * @throws UserException
     */
    public static function getNotice(int $id)
    {
        $notice = Notice::find($id);

        if ($notice->trashed()) {
            throw new UserException('公告不存在,请刷新后再试');
        }
        return $notice;
    }

    public static function readNotice($user, $noticeId)
    {
        $notice = Notice::find($noticeId);

        if ($notice->trashed()) {
            return false;
        }

        return $user->readNotices()->sync($notice->id);
    }
}
