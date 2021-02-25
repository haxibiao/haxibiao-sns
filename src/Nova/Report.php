<?php

namespace Haxibiao\Sns\Nova;

use App\Nova\Article;
use App\Nova\User;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Resource;

class Report extends Resource
{
    public static $model  = 'App\Report';
    public static $title  = 'id';
    public static $search = [
        'id',
    ];

    public static $group = "数据中心";
    public static function label()
    {
        return '举报';
    }

    public static $with = ['user', 'reportable'];

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('发起用户', 'user', User::class)->exceptOnForms(),
            MorphTo::make('举报对象', 'reportable')->types([
                Article::class,
                Comment::class,
                User::class,
            ]),
            Textarea::make('理由', 'reason')->exceptOnForms(),
            Select::make('状态', 'status')->options($this::getStatuses())->displayUsingLabels(),
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
        return [
            new \Haxibiao\Breeze\Nova\Filters\Report\ReportStatusFilter,
            new \Haxibiao\Breeze\Nova\Filters\Report\ReportTypeFilter,
        ];
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
        return [
            new \App\Nova\Actions\Report\AuditReport,
        ];
    }
}
