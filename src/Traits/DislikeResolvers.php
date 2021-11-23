<?php
/**
 * @Author  guowei<gongguowei01@gmail.com>
 * @Data    2020/5/18
 * @Version
 */

namespace Haxibiao\Sns\Traits;

use Haxibiao\Sns\Dislike;

trait DislikeResolvers
{
    public function resolveStore($user, array $inputs)
    {
        $id   = data_get($inputs, 'id',data_get($inputs,'notlike_id'));
        $type = data_get($inputs, 'type',data_get($inputs,'notlike_type'));
        app_track_event("用户操作","设为不喜欢","不喜欢对象为: $id, 不喜欢类型为: $type");

        return Dislike::store($id, $type, getUser());
    }
}
