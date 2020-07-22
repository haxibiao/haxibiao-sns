<?php

namespace Haxibiao\Sns;


use Haxibiao\Sns\Traits\FollowAttrs;
use Haxibiao\Sns\Traits\FollowRepo;
use Haxibiao\Sns\Traits\FollowResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Follow extends Model
{
    use SoftDeletes;
    use FollowAttrs;
    use FollowRepo;
    use FollowResolvers;

    protected $fillable = [
        'user_id',
        'followed_type',
        'followed_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function followed()
    {
        return $this->morphTo();
    }
}
