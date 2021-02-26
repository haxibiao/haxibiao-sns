<?php

namespace Haxibiao\Sns\Traits;

use Haxibiao\Sns\UserBlock;

trait Blockable
{

    //用户的黑名单（屏蔽其他用户，不感兴趣动态）
    public function userBlocks()
    {
        return $this->morphMany(UserBlock::class, 'blockable');
    }

}
