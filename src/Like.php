<?php

namespace App;

use App\Traits\Like\LikeAttrs;
use App\Traits\Like\LikeRepo;
use App\Traits\Like\LikeResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Like extends Model
{
    use SoftDeletes;
    use LikeAttrs;
    use LikeRepo;
    use LikeResolvers;

    protected $fillable = [
        'user_id',
        'likable_id',
        'likable_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\User::class);
    }

    public function likable(): MorphTo
    {
        return $this->morphTo();
    }
}
