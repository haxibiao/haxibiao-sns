<?php

namespace Haxibiao\Sns;

use Haxibiao\Sns\Traits\FavoriteAttrs;
use Haxibiao\Sns\Traits\FavoriteRepo;
use Haxibiao\Sns\Traits\FavoriteResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorite extends Model
{
    // use SoftDeletes;
    use FavoriteAttrs;
    use FavoriteRepo;
    use FavoriteResolvers;

    protected $guarded = [
    ];

    public static function boot()
    {
        parent::boot();
        static::observe(\Haxibiao\Sns\Observers\FavoriteObserver::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\User');
    }

    public function favorable(): MorphTo
    {
        return $this->morphTo();
    }

    public function faved()
    {
        return $this->morphTo('favorable');
    }

    public function target()
    {
        return $this->morphTo('favorable');
    }

    public function diagram()
    {
        return $this->belongsTo('\App\Diagram', 'favorable_id');
    }
}