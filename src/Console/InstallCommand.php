<?php

namespace Haxibiao\Sns\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InstallCommand extends Command
{

    /**
     * The name and signature of the Console command.
     *
     * @var string
     */
    protected $signature = 'sns:install';

    /**
     * The Console command description.
     *
     * @var string
     */
    protected $description = '安装 haxibiao/sns';

    /**
     * Execute the Console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('强制发布资源');
        $this->call('vendor:publish', [
            '--tag'   => 'sns-config',
            '--force' => true,
        ]);
        $this->call('vendor:publish', [
        '--tag'   => 'sns-graphql',
        '--force' => true,
    ]);

        $this->call('vendor:publish', [
            '--tag'   => 'sns-tests',
            '--force' => true,
        ]);



        $this->comment("复制 stubs ...");
        copy(__DIR__ . '/stubs/Like.stub', app_path('Like.php'));
        copy(__DIR__ . '/stubs/NotLike.stub', app_path('NotLike.php'));
        copy(__DIR__ . '/stubs/Follow.stub', app_path('Follow.php'));
        copy(__DIR__ . '/stubs/Report.stub', app_path('Report.php'));
        copy(__DIR__ . '/stubs/Comment.stub', app_path('Comment.php'));
        copy(__DIR__ . '/stubs/Favorite.stub', app_path('Favorite.php'));
    }
}
