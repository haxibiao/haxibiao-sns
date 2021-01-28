<?php

namespace Haxibiao\Sns;

use App\User;
use Haxibiao\Sns\Traits\FollowAttrs;
use Haxibiao\Sns\Traits\FollowRepo;
use Haxibiao\Sns\Traits\FollowResolvers;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    // use SoftDeletes;
    use FollowAttrs;
    use FollowRepo;
    use FollowResolvers;

    protected $fillable = [
        'user_id',
        'followable_type',
        'followable_id',
    ];

    public static function boot()
    {
        parent::boot();

        self::created(function ($follow) {
            if ($follow->followable_type == 'users') {
                //更新用户的关注数 //FIXME: 以前从来没count 过，需要fixdata count一次做基础...
                $user = $follow->user;
                $user->profile->increment('follows_count');
                //更新被关注用户的粉丝数
                if ($followable = $follow->followable) {
                    $followable->profile->increment('followers_count');
                }
            }
        });
        self::deleted(function ($follow) {
            if ($follow->followable_type == 'users') {
                //更新用户的关注数
                $user = $follow->user;
                $user->profile->decrement('follows_count');

                //更新被关注用户的粉丝数
                if ($followable = $follow->followable) {
                    $followable->profile->decrement('followers_count');
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //FIXME::慢慢用followable取代这个function
    public function followed()
    {
        return $this->morphTo('followable');
    }

    public function followable()
    {
        return $this->morphTo();
    }
}
