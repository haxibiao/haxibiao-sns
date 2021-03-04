<?php

namespace Haxibiao\Sns\Nova;

use App\Nova\User;
use Laravel\Nova\Resource;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;

class Notice extends Resource
{
    // public static $displayInNavigation = true;
    public static $model  = 'App\Notice';
    public static $title  = 'title';
    public static $search = [
        'title',
    ];

    public static $group = "用户中心";
    public static function label()
    {
        return '通告';
    }

    public static $with = ['user'];

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('标题', 'title'),
            Textarea::make('内容', 'content'),
            DateTime::make('到期时间', 'expires_at'),
            BelongsTo::make('发表人', 'user', User::class)->exceptOnForms(),
            DateTime::make('创建时间', 'created_at')->exceptOnForms(),
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
