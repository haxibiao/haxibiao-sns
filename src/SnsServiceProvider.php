<?php

namespace Haxibiao\Sns;

use Haxibiao\Sns\Console\InstallCommand;
use Haxibiao\Sns\Console\PublishCommand;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class SnsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->bindPathsInContainer();

        $this->registerMorphMap();

        $this->mergeConfigFrom(
            __DIR__ . '/../config/sns.php',
            'sns'
        );
        $this->commands([
            InstallCommand::class,
            PublishCommand::class,
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot()
    {
        $this->bindObservers();

        //加载路由
        $this->loadRoutesFrom(
            $this->app->make('path.haxibiao-sns') . '/router.php'
        );

        //安装时需要
        if ($this->app->runningInConsole()) {

            //加载数据库迁移
            $this->loadMigrationsFrom($this->app->make('path.haxibiao-sns.migrations'));

            //发布 资源
            $this->publishes([
                __DIR__ . '/../config/sns.php' => config_path('sns.php'),
                __DIR__ . '/../graphql'        => base_path('graphql/sns'),
            ], 'sns-graphql');
        }
    }

    public function bindObservers()
    {
        \Haxibiao\Sns\Feedback::observe(\Haxibiao\Sns\Observers\FeedbackObserver::class);
        \Haxibiao\Sns\Message::observe(\Haxibiao\Sns\Observers\MessageObserver::class);
        \Haxibiao\Sns\Comment::observe(\Haxibiao\Sns\Observers\CommentObserver::class);
        \Haxibiao\Sns\Like::observe(\Haxibiao\Sns\Observers\LikeObserver::class);
        \Haxibiao\Sns\Follow::observe(\Haxibiao\Sns\Observers\FollowObserver::class);
        \Haxibiao\Sns\Report::observe(\Haxibiao\Sns\Observers\ReportObserver::class);
        \Haxibiao\Sns\Notice::observe(\Haxibiao\Sns\Observers\NoticeObserver::class);
    }

    protected function bindPathsInContainer()
    {
        foreach ([
            'path.haxibiao-sns'            => $root = dirname(__DIR__),
            'path.haxibiao-sns.config'     => $root . '/config',
            'path.haxibiao-sns.database'   => $database = $root . '/database',
            'path.haxibiao-sns.migrations' => $database . '/migrations',
            'path.haxibiao-sns.graphql'    => $root . '/graphql',
        ] as $abstract => $instance) {
            $this->app->instance($abstract, $instance);
        }
    }

    protected function registerMorphMap()
    {
        $this->morphMap([
            'feedbacks'   => \App\Feedback::class,
            'comments'    => \App\Comment::class,
            'users'       => \App\User::class,
            'collections' => \App\Collection::class,
        ]);
    }

    protected function morphMap(array $map = null, bool $merge = true): array
    {
        return Relation::morphMap($map, $merge);
    }
}
