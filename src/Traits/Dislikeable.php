<?php

namespace Haxibiao\Sns\Traits;

use App\NotLike;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Dislikeable
{
    public function notLikes(): HasMany
    {
        return $this->hasMany(NotLike::class);
    }
}
