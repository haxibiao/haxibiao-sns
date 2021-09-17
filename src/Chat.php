<?php

namespace Haxibiao\Sns;

use Haxibiao\Breeze\Model;
use Haxibiao\Breeze\Traits\HasFactory;
use Haxibiao\Breeze\User;
use Haxibiao\Sns\ChatUser;
use Haxibiao\Sns\Traits\ChatAttrs;
use Haxibiao\Sns\Traits\ChatRepo;
use Haxibiao\Sns\Traits\ChatResolvers;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    use HasFactory;
    use ChatAttrs;
    use ChatRepo;
    use ChatResolvers;

    protected $guarded = [];

    public static function boot()
    {
        parent::boot();
        static::observe(new \Haxibiao\Sns\Observers\ChatObserver);
    }

    protected $casts = [
        'uids' => 'array',
    ];

    //最小成员数
    const MIN_USERS_NUM = 2;
    const MAX_USERS_NUM = 100;

    /**
     * 类型
     */
    const SINGLE_TYPE  = 0;
    const GROUP_TYPE   = 1;
    const MEET_UP_TYPE = 2;
    const BUSINESS_ALLIANCE_TYPE = 3; // 买条街-联盟订单-商户群

    /**
     * 状态
     * 1公开
     * 0私密
     */
    const PUBLIC_STATUS  = 1;
    const PRIVATE_STATUS = 0;

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function users(): BelongsToMany
    {
        $qb = $this->belongsToMany(User::class)
            ->using(ChatUser::class)
            ->withTimestamps();
        $qb->withPivot('unreads');
        return $qb;
    }

    public function lastMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    /**
     * 包含成员
     *
     * @param [int] $uid
     * @return bool
     */
    public function containsMembers($uid)
    {
        return array_search($uid, $this->uids) !== false;
    }

    public function scopePublishStatus($query)
    {
        return $query->where('status', Chat::PUBLIC_STATUS);
    }

    public function scopePrivateStatus($query)
    {
        return $query->where('status', Chat::PRIVATE_STATUS);
    }

    public static function getStatus()
    {
        return [
            self::PUBLIC_STATUS  => '公开',
            self::PRIVATE_STATUS => '私密',
        ];
    }

    public static function getTypes()
    {
        return [
            self::SINGLE_TYPE => '私聊',
            self::GROUP_TYPE  => '群聊',
        ];
    }
}
