<?php

namespace Haxibiao\Sns;


use App\Traits\Favorite\FavoriteAttrs;
use App\Traits\Favorite\FavoriteRepo;
use App\Traits\Favorite\FavoriteResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Favorite extends Model
{
    use SoftDeletes;
    use FavoriteAttrs;
    use FavoriteRepo;
    use FavoriteResolvers;

    protected $fillable = [
        'user_id',
        'favorable_id',
        'favorable_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\User');
    }

    public function favorable(): MorphTo
    {
        return $this->morphTo();
    }
}
