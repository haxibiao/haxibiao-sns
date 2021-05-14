<?php
/**
 * @Author  guowei<gongguowei01@gmail.com>
 * @Data    2020/5/18
 * @Version
 */

namespace Haxibiao\Sns\Traits;

use Haxibiao\Sns\Visit;

trait NoticeAttrs
{

    //标记最近更新时间是1小时内的，算已读
    public function getIsReadAttribute()
    {
        return $this->updated_at > now()->subHour();
    }

    public function getRouteAttribute()
    {
        return 'NoticeItemDetail';
    }

    /**
     * attribute
     **/
    //通知消息是否已读
    public function getUnreadAttribute()
    {
        if ($user = getUser(false)) {
            return !Visit::where("user_id", $user->id)
                ->where("visited_type", 'notices')
                ->where("visited_id", $this->id)
                ->exists();
        }
        return null;
    }

    public static function getTypes()
    {
        return [
            'activity'      => '活动通知',
            'deduction'     => '扣款',
            'public_notice' => '全体通知',
            'others'        => '其他通知',
        ];
    }
}
