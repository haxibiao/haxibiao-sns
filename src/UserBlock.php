<?php

namespace Haxibiao\Sns;

use App\User;
use Haxibiao\Sns\Traits\UserBlockResolvers;
use Illuminate\Database\Eloquent\Model;

class UserBlock extends Model
{
    use UserBlockResolvers;

    public $guarded = [

    ];

    public function blockable()
    {
        return $this->morphTo("blockable");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
