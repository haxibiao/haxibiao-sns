<?php

namespace Haxibiao\Sns;

use App\User;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ChatUser extends Pivot
{
    protected $fillable = [
        'user_id',
        'chat_id',
        'unreads_count',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
