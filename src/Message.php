<?php

namespace Haxibiao\Sns;

use App\User;
use Haxibiao\Breeze\Model;
use Haxibiao\Breeze\Traits\HasFactory;
use Haxibiao\Sns\Chat;
use Haxibiao\Sns\Traits\MessageAttrs;
use Haxibiao\Sns\Traits\MessageRepo;
use Haxibiao\Sns\Traits\MessageResolvers;

class Message extends Model
{
    use MessageAttrs;
    use MessageRepo;
    use MessageResolvers;
    use HasFactory;

    protected $touches = ['chat'];
    protected $guarded = [];

    protected $casts = [
        'body'    => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * 消息类型 type
     * 文字
     * 图片
     * 语音
     * 视频
     * 电影
     *
     */
    const TEXT_TYPE        = 0;
    const IMAGE_TYPE       = 1;
    const AUDIO_TYPE       = 2;
    const VIDEO_TYPE       = 3;
    const MOVIE_CARD_TYPE  = 4;
    const MEETUP_CARD_TYPE = 5;
    const MOVIE_ROOM       = 6;

    public static function boot()
    {
        parent::boot();
        static::observe(\Haxibiao\Sns\Observers\MessageObserver::class);
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messageable()
    {
        return $this->morphTo();
    }
}
