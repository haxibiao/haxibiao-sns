<?php

namespace Haxibiao\Sns;

use Haxibiao\Sns\Traits\DislikeRepo;
use Haxibiao\Sns\Traits\DislikeResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Dislike extends Model
{
    use DislikeRepo;
    use DislikeResolvers;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function dislikeable(): morphTo
    {
        return $this->morphTo();
    }

    public function scopeByType($query, $value)
    {
        return $query->where('dislikeable_type', $value);
    }

    public function scopeByDislikableId($query, $id)
    {
        return $query->where('dislikeable_id', $id);
    }

    /**
     * 兼容旧属性
     */
    public function notLikeable(): morphTo
    {
        return $this->morphTo('dislikeable');
    }
}
