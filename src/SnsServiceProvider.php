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
            $this->publishes([
                __DIR__ . '/../graphql/live' => base_path('graphql/live'),
            ], 'sns-graphql');


            // 发布 Nova
            $this->publishes([
                __DIR__ . '/Nova' => base_path('app/Nova'),
            ], 'sns-nova');

            //发布 tests
            $this->publishes([
                __DIR__ . '/../tests/Feature/GraphQL'         => base_path('tests/Feature/GraphQL'),
            ], 'sns-tests');

            //发布 factories
        }

    }

    protected function bindPathsInContainer()
    {

    }

    protected function registerMorphMap()
    {
        $this->morphMap([
            'questions' => 'App\Question',
            'videos' => 'App\Video',
            'comments' => 'Haxibiao\Sns\Comment',
            'posts' => 'Haxibiao\Content\Post',

        ]);
    }

    protected function morphMap(array $map = null, bool $merge = true): array
    {
        return Relation::morphMap($map, $merge);
    }
}
