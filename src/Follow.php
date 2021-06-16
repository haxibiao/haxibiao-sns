<?php

namespace Haxibiao\Sns;

use App\User;
use Haxibiao\Sns\Traits\FollowAttrs;
use Haxibiao\Sns\Traits\FollowRepo;
use Haxibiao\Sns\Traits\FollowResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        static::observe(\Haxibiao\Sns\Observers\FollowObserver::class);
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
