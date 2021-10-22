<?php

namespace Haxibiao\Sns\Traits;

use Haxibiao\Breeze\Events\NewNotice;
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

        $notices = Notice::getNoticesQuery(get_referer(), getDeviceRootBrand())
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

    public static function getNoticesQuery($appStore, $brand)
    {
        $qb = Notice::where('expires_at', '>', now())->latest('id');

        if (config('app.name') == "datizhuanqian") {
            $qb = $qb->where(function ($qb) use ($appStore) {
                $qb->when($appStore, function ($qb) use ($appStore) {
                    $qb->where('store', $appStore);
                })
                    ->OrWhereNull('store');
            })->where(function ($qb) use ($brand) {
                $qb->when($brand, function ($qb) use ($brand) {
                    $qb->where('brand', $brand);
                })
                    ->OrWhereNull('brand');
            });

            $disableNoticeTitles = \App\NoticeVersionControl::query()
                ->where(function ($qb) use ($appStore) {
                    $qb->when($appStore, function ($qb) use ($appStore) {
                        $qb->where('store', $appStore)
                            ->OrWhereNull('store');
                    });
                })
                ->where('status', 0)
                ->pluck('title')
                ->toArray();
            if ($disableNoticeTitles) {
                $qb = $qb->whereNotIn('title', $disableNoticeTitles);
            }
        }
        return $qb;
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

    public static function getPushNotice($store)
    {
        if ($store) {
            return Notice::where('user_id', 1)->where('store', $store)->latest('id')->first();
        }
        return null;
    }

    public static function readNotice($user, $noticeId)
    {
        $notice = Notice::find($noticeId);

        if ($notice->trashed()) {
            return false;
        }

        return $user->readNotices()->sync($notice->id);
    }

    public static function pushUnReadNotice($user)
    {
        //获取未读的官方通知
        $readNoticeIds = $user->readNotices()->pluck('notice_id')->toArray();
        $notice        = Notice::getNoticesQuery(get_referer(), getDeviceRootBrand())
            ->where('user_id', 1)
            ->when(count($readNoticeIds), function ($qb) use ($readNoticeIds) {
                $qb->whereNotIn('id', $readNoticeIds);
            })
            ->first();
        //发送给用户
        if ($notice) {
            dispatch(new \Haxibiao\Breeze\Events\NewNotice($notice, $user->id))->delay(3);
            //标记已读
            $user->readNotices()->attach($notice->id);

        }

    }
}
