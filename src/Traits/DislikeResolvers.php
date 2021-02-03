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
        $id   = data_get($inputs, 'id',data_get($inputs,'notlike_id'));
        $type = data_get($inputs, 'type',data_get($inputs,'notlike_type'));

        return Dislike::store($id, $type, getUser());
    }
}
