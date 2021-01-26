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
                __DIR__ . '/../graphql'        => base_path('graphql'),
            ], 'sns-graphql');
        }
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
            'comments' => \App\Comment::class,
        ]);
    }

    protected function morphMap(array $map = null, bool $merge = true): array
    {
        return Relation::morphMap($map, $merge);
    }
}
