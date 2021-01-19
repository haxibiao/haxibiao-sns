<?php

namespace Haxibiao\Sns;

use Haxibiao\Breeze\Model;
use Haxibiao\Breeze\User;
use Haxibiao\Sns\Traits\TipResolvers;

class Tip extends Model
{
    use TipResolvers;

    public $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tipable()
    {
        return $this->morphTo();
    }

    //actionable target, 动态 - 打赏了 - 文章
    public function target()
    {
        return $this->morphTo();
    }
}
