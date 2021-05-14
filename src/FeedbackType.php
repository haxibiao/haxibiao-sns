<?php

namespace Haxibiao\Sns;

use Illuminate\Database\Eloquent\Model;

class FeedbackType extends Model
{
    protected $fillable = [
        'name',
        'count',
    ];

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }
}
