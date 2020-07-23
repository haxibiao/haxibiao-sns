<?php

namespace Haxibiao\Sns;


use Haxibiao\Sns\Traits\LikeAttrs;
use Haxibiao\Sns\Traits\LikeRepo;
use Haxibiao\Sns\Traits\LikeResolvers;
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

  protected  $guarded=[];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\User::class);
    }

    public function likable(): MorphTo
    {
        return $this->morphTo();
    }
}
