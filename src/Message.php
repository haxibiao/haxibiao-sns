<?php

namespace Haxibiao\Sns;

use Haxibiao\Breeze\Model;

class Message extends Model
{
    protected $touches = ['chat'];

    public $fillable = [
        'user_id',
        'chat_id',
        'message',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
