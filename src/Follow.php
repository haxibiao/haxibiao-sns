<?php

namespace Haxibiao\Sns;

use App\User;
use Haxibiao\Sns\Traits\FollowAttrs;
use Haxibiao\Sns\Traits\FollowRepo;
use Haxibiao\Sns\Traits\FollowResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Follow extends Model
{
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

    public function followed()
    {
        return $this->morphTo('followable');
    }

    public function followable()
    {
        return $this->morphTo();
    }

    public function people(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'followable_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Category::class, 'followable_id');
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(\App\Collection::class, 'followable_id');
    }
}
