<?php

namespace Haxibiao\Sns;

use Illuminate\Database\Eloquent\Relations\Pivot;

class FeedbackImage extends Pivot
{
    protected $fillable = [
        'feedback_id',
        'image_id',
    ];

    public function user()
    {
        return $this->belongTo(User::class);
    }
}
