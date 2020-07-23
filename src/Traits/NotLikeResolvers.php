<?php
/**
 * @Author  guowei<gongguowei01@gmail.com>
 * @Data    2020/5/18
 * @Version
 */

namespace Haxibiao\Sns\Traits;

use Illuminate\Support\Arr;

trait NotLikeResolvers
{
    public function resolveStore($user, array $inputs)
    {
        //TODO：从原graphql中得知$id与$type相等，调用该API也只传递了$id，但是后端却还设计了一个type字段？
        $id = Arr::get($inputs, 'notlike_id');
        return self::store($id, $id, getUser());
    }
}
