<?php

namespace Haxibiao\Sns;

use Haxibiao\Breeze\Model;
use Haxibiao\Breeze\Traits\HasFactory;
use Haxibiao\Breeze\User;
use Haxibiao\Sns\Chat;
use Haxibiao\Sns\Traits\MessageRepo;
use Haxibiao\Sns\Traits\MessageResolvers;

class Message extends Model
{
    use MessageRepo, MessageResolvers;
    use HasFactory;
    protected $touches = ['chat'];
    protected $guarded = [
    ];

    protected $casts = [
        'body'    => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * 消息类型 type
     * 文字
     * 媒体
     * 文件
     */
    const TEXT_TYPE  = 0;
    const MEDIA_TYPE = 1;
    const FILE_TYPE  = 2;

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function toJsonBody()
    {
        $json = [];
        $body = $this->body;
        if (is_string($body)) {
            $json['text'] = $body;
        } else {
            $json = $body;
        }
        return $json;
    }
}
