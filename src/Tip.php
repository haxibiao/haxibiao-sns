<?php

namespace Haxibiao\Sns;

use Haxibiao\Breeze\Model;

class Tip extends Model
{
    public $guarded = [];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
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
