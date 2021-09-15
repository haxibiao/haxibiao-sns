<?php

namespace Haxibiao\Sns\Nova;

use App\Nova\User;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;

class Chat extends Resource
{
    public static $model  = 'App\Chat';
    public static $title  = 'id';
    public static $search = [
        'id',
    ];

    public static $group = "数据中心";
    public static function label()
    {
        return '聊天';
    }

    public static $with = ['users'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('群名', 'subject'),
            Text::make('群公告', 'introduction'),
            BelongsTo::make('群主', 'user', User::class)->exceptOnForms(),
            HasMany::make('用户', 'users', User::class)->exceptOnForms(),
            Text::make('最后一条消息', function () {
                return data_get($this, 'lastMessage.message');
            }),
            Select::make('状态', 'status')->options(\App\Chat::getStatus())->default(\App\Chat::PUBLIC_STATUS)->displayUsingLabels(),
            Select::make('类型', 'type')->options(\App\Chat::getTypes())->default(\App\Chat::GROUP_TYPE)->displayUsingLabels(),
            Image::make('群头像', 'icon')->store(
                function (Request $request, $model) {
                    $file = $request->file('icon');
                    return $model->saveDownloadImage($file);
                })->thumbnail(function () {
                return $this->icon_url;
            })->preview(function () {
                return $this->icon_url;
            }),

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
        return [
            // new Metrics\Chat\ChatsPerDay,
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
