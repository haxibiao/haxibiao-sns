<?php

namespace Haxibiao\Sns\Nova;

use App\Nova\Article;
use App\Nova\Comment;
use App\Nova\User;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Resource;

class Action extends Resource
{

    public static $model = 'App\Action';

    public static $displayInNavigation = true;

    public static $title  = 'id';
    public static $search = [
        'id',
    ];

    public static $group = '数据中心';
    public static function label()
    {
        return "行为";
    }

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('用户', 'user', User::class),
            MorphTo::make('用户行为', 'actionable')->types([
                Article::class,
                Comment::class,
                Like::class,
                Favorite::class,
            ]),
            DateTime::make('创建时间', 'created_at'),
        ];
    }
}
