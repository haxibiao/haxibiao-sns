<?php

namespace Haxibiao\Sns;

use Haxibiao\Sns\Console\InstallCommand;
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

            $this->publishes([
                __DIR__ . '/../config/sns.php' => config_path('sns.php'),
            ], 'sns-config');

            //发布 graphql
            $this->publishes([
                __DIR__ . '/../graphql' => base_path('graphql'),
            ], 'sns-graphql');

            // //发布 tests
            // $this->publishes([
            //     __DIR__ . '/../tests/Feature/GraphQL' => base_path('tests/Feature/GraphQL'),
            // ], 'sns-tests');

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
