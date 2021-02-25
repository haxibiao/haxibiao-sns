<?php

namespace Haxibiao\Sns\Nova;

use App\Nova\User;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Resource;

class Message extends Resource
{
    public static $model  = 'App\Message';
    public static $title  = 'id';
    public static $search = [
        'id',
    ];

    public static $group = "数据中心";
    public static function label()
    {
        return '消息';
    }

    public static $with = ['user', 'chat'];
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('用户', 'user', User::class)->exceptOnForms(),
            BelongsTo::make('私聊', 'chat', Chat::class)->exceptOnForms(),
            Code::make('消息内容', 'body')->json(JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE)->exceptOnForms(),
            DateTime::make('阅读时间', 'read_at')->exceptOnForms(),
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
        return [
//            new Metrics\Message\MessagesPerDay,
//            new Metrics\Message\DailyUsersCount,
        ];
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
