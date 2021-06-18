<?php

namespace Haxibiao\Sns\Traits;

use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Sns\Visit;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

trait VisitResolvers
{
    public function resolveSaveVisits($root, $args, $context, $info)
    {
        $user = getUser();
        $user->addPostPlaysNumber(date('Ymd'), count($args['input']));
        $user->reviewTasksByClass(get_class(new Visit));
        return;
    }

    public function getVisits($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = User::find($args['user_id']);
        return $user->visits()->where('visited_type', $args['visitType'])
            ->latest('id');
    }

    public function getByDate($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if (isset($args['filter'])) {
            if ($args['filter'] == 'TODAY') {
                return Visit::where('created_at', '>=', today());
            } else if ($args['filter'] == 'EARLY') {
                return Visit::where('created_at', '<', today());
            }
        }
    }

    public function resolveCreateVisit($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = getUser(false);
        if ($user) {
            foreach (data_get($args, 'visited_id') as $visitedId) {
                Visit::firstOrCreate([
                    'user_id'      => $user->id,
                    'visited_id'   => $visitedId,
                    'visited_type' => $args['visited_type'],
                ]);
            }
            return true;
        }
        return false;
    }

    public function recordVisitTime($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user     = getUser();
        $duration = data_get($args, 'duration');
        $id       = data_get($args, 'visited_id');
        $type     = data_get($args, 'visited_type');
        $visit    = Visit::firstOrCreate([
            'visited_id'   => $id,
            'visited_type' => $type,
            'user_id'      => $user->id,
        ]);
        $visit->duration += $duration;
        $visit->save();
        $user->reviewTasksByClass('Custom');
        if ($type == "movies") {
            //记录长视频观看行为时长
            app_track_event('长视频', '观看电影', '观看时长（min）', $duration / 60);
        }
        return $visit;
    }
}
