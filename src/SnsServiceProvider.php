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

            //发布 graphql
            $this->publishes([
                __DIR__ . '/../graphql/like' => base_path('graphql/like'),
            ], 'sns-graphql');


            //发布 tests
            $this->publishes([
                __DIR__ . '/../tests/Feature/GraphQL'         => base_path('tests/Feature/GraphQL'),
            ], 'sns-tests');

        }

    }

}
