<?php

namespace Haxibiao\Sns;

use Haxibiao\Breeze\User;
use Haxibiao\Sns\Traits\VisitAttrs;
use Haxibiao\Sns\Traits\VisitRepo;
use Haxibiao\Sns\Traits\VisitResolvers;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use VisitResolvers;
    use VisitAttrs;
    use VisitRepo;

    protected $fillable = [
        'user_id',
        'visited_id',
        'visited_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function visitable()
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

    public static function saveVisits($user, $visits, $visitableType)
    {
        $visitsObj = [];
        foreach ($visits as $visit) {
            $visitable = [
                'visited_type' => $visitableType,
                'visited_id'   => $visit->id,
                'user_id'      => $user->id,
            ];
            array_push($visitsObj, $visitable);
        }
        return Visit::insert($visitsObj);
    }
}
