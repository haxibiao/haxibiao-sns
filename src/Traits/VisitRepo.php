<?php

namespace Haxibiao\Sns\Traits;

use Haxibiao\Sns\Visit;

trait VisitRepo
{
    public static function createVisit($user_id, $visited_id, $visited_type)
    {
        return Visit::firstOrCreate([
            'user_id'      => $user_id,
            'visited_id'   => $visited_id,
            'visited_type' => $visited_type,
            'duration' => 0
        ]);
    }

    public static function createvisits($user_id, $visited_id, $visited_type)
    {
        return Visit::create([
            'user_id'      => $user_id,
            'visited_id'   => $visited_id,
            'visited_type' => $visited_type,
        ]);
    }
}
