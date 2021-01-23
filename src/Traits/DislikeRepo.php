<?php
/**
 * @Author  guowei<gongguowei01@gmail.com>
 * @Data    2020/5/18
 * @Version
 */

namespace Haxibiao\Sns\Traits;

use App\Exceptions\UserException;
use App\User;

trait DislikeRepo
{

    /**
     * 不感兴趣，减少推荐用
     *
     * @param int $id
     * @param string $type
     * @param User $user
     * @return mixed
     * @throws \Throwable
     */
    public static function store(int $id, string $type, User $user)
    {
        //该记录是否存在
        $notLiked = static::firstOrNew([
            'dislikeable_id'   => $id,
            'dislikeable_type' => $type,
            'user_id'          => $user->id,
        ]);
        throw_if(isset($notLiked->id), UserException::class, '屏蔽失败,该用户已屏蔽过了!');
        $notLiked->save();
        return $notLiked;
    }
}
