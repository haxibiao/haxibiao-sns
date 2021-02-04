<?php

namespace Haxibiao\Sns;

use Haxibiao\Sns\Traits\LikeAttrs;
use Haxibiao\Sns\Traits\LikeRepo;
use Haxibiao\Sns\Traits\LikeResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Like extends Model
{
    use LikeAttrs;
    use LikeRepo;
    use LikeResolvers;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\User::class);
    }

    // 兼容旧接口用
    public function liked()
    {
        return $this->morphTo();
    }

    public function likable(): MorphTo
    {
        return $this->morphTo('likable');
    }

    public function scopeByLikableType($query, $likableType)
    {
        return $query->where('likable_type', $likableType);
    }

    public function scopeByLikableId($query, $likableId)
    {
        return $query->where('likable_id', $likableId);
    }

    public function scopeByUserId($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
