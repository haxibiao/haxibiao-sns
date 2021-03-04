<?php

namespace Haxibiao\Sns;

use App\User;
use App\Visit;
use Haxibiao\Breeze\Traits\HasFactory;
use Haxibiao\Sns\Traits\NoticeRepo;
use Haxibiao\Sns\Traits\NoticeResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Notice extends Model
{
    use HasFactory;
    use NoticeResolvers;
    use NoticeRepo;

    protected $fillable = [
        'user_id',
        'to_user_id',
        'type',
        'title',
        'content',
        'expires_at',
    ];

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

    /**
     * scope
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now())
            ->orWhereNull('expires_at');
    }

    /**
     * attribute
     **/
    //通知消息是否已读
    public function getUnreadAttribute()
    {
        if ($user = getUser(false)) {
            return !Visit::where("user_id", $user->id)
                ->where("visited_type", 'notices')
                ->where("visited_id", $this->id)
                ->exists();
        }
        return null;
    }
    public static function getTypes()
    {
        return [
            'activity'      => '活动通知',
            'deduction'     => '扣款',
            'public_notice' => '全体通知',
            'others'        => '其他通知',
        ];
    }
}
