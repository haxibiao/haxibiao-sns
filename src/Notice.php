<?php

namespace Haxibiao\Sns;

use App\User;
use App\Visit;
use Haxibiao\Breeze\Traits\HasFactory;
use Haxibiao\Sns\Traits\NoticeAttrs;
use Haxibiao\Sns\Traits\NoticeRepo;
use Haxibiao\Sns\Traits\NoticeResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    use HasFactory;
    use SoftDeletes;
    use NoticeRepo, NoticeAttrs, NoticeResolvers;

    protected $guarded = [];

    //活动通知
    const ACTIVITY = 'activity';
    //扣款通知
    const DEDUCTION = 'deduction';
    //其他通知
    const OTHERS = 'others';
    //系统通知
    const PUBLIC_NOTICE = 'public_notice';

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
        static::observe(\Haxibiao\Sns\Observers\NoticeObserver::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notifyUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
    public function visits(): MorphMany
    {
        return $this->morphMany(Visit::class, 'visited');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->using(NoticeUser::class)
            ->withPivot('created_at');
    }

    /**
     * scope
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now())
            ->orWhereNull('expires_at');
    }
}
