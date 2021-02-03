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

    // 兼容旧属性
    public function target()
    {
        return $this->morphTo();
    }
}
