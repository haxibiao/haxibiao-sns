<?php

namespace Haxibiao\Sns;

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

//        $this->mergeConfigFrom(
//            __DIR__ . '/../config/haxibiao-categorized.php',
//            'haxibiao-categorized'
//        );

        $this->commands([
            InstallCommand::class,
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot()
    {
        //安装时需要
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom($this->app->make('path.haxibiao-category.migrations'));

            //发布config

            //发布 graphql

            // 发布 Nova

            //发布 tests

            //发布 factories

        }

        $this->loadRoutesFrom(
            $this->app->make('path.haxibiao-category') . '/router.php'
        );

        //绑定observers
        \Haxibiao\Media\Spider::observe(Observers\SpiderObserver::class);
    }

    protected function bindPathsInContainer()
    {

    }

    protected function registerMorphMap()
    {
        $this->morphMap([
        ]);
    }

    protected function morphMap(array $map = null, bool $merge = true): array
    {
        return Relation::morphMap($map, $merge);
    }
}
