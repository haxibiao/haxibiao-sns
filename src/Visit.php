<?php

namespace Haxibiao\Sns;

use Haxibiao\Breeze\User;
use Haxibiao\Sns\Traits\VisitAttrs;
use Haxibiao\Sns\Traits\VisitRepo;
use Haxibiao\Sns\Traits\VisitResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Schema;

class Visit extends Model
{
    use VisitResolvers;
    use VisitAttrs;
    use VisitRepo;

    protected $guarded = [
    ];

    const REAL_VISITED = 1;
    const FAKE_VISITED = 0;

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\User::class);
    }

    public function visitable()
    {
        return $this->morphTo();
    }

    public function visited(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeOfType($query, $value)
    {
        return $query->where('visited_type', $value);
    }

    public function scopeOfUserId($query, $value)
    {
        return $query->where('user_id', $value);
    }

    public static function saveVisits($user, $visits, $visitedType = 'posts', $status = 1)
    {
        foreach ($visits as $visit) {
            $visited = [
                'visited_type' => $visitedType,
                'visited_id'   => $visit['id'],
                'user_id'      => $user->id,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
            if (Schema::hasColumn('visits', 'status')) {
                $visited = array_add($visited, "status", $status);
            }
            Visit::insert($visited);

            $user->reviewTasksByClass(__class__);
        }
    }

    //repo
    public function getVisits($user, $limit = 10, $offset = 0)
    {
        $visits = Visit::where('user_id', $user->id)->take($limit)->skip($offset);
        return $visits;
    }

}
