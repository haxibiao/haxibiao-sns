<?php
/**
 * @Author  guowei<gongguowei01@gmail.com>
 * @Data    2020/5/18
 * @Version
 */

namespace Haxibiao\Sns\Traits;

use Haxibiao\Sns\Dislike;
use Illuminate\Support\Arr;

trait DislikeResolvers
{
    public function resolveStore($user, array $inputs)
    {
        $id   = Arr::get($inputs, 'id');
        $type = Arr::get($inputs, 'type');

        return Dislike::store($id, $type, getUser());
    }
}
