<?php

namespace Haxibiao\Sns\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Resource;

class Comment extends Resource
{
    public static $model = 'App\Comment';
    public static $title = 'body';

    public static $group  = "数据中心";
    public static $search = [
        'id', 'body',
    ];

    public static function label()
    {
        return "评论";
    }
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('作者', 'user', User::class)->hideWhenCreating(),
            Text::make('评论内容', function () {
                $text = $this->title ?? str_limit($this->body);
                return $text;
            })->onlyOnIndex(),
            Textarea::make('内容', 'body')->rules('required')->hideFromIndex(),
            MorphTo::make('评论对象', 'commentable')->types([
                Article::class,
                Video::class,
                Feedback::class,
            ]),
            DateTime::make('创建时间', 'created_at')->hideWhenCreating(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
