<?php

namespace Haxibiao\Sns;

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
            '--tag'   => 'sns-nova',
            '--force' => true,
        ]);

        $this->call('vendor:publish', [
            '--tag'   => 'sns-tests',
            '--force' => true,
        ]);

        $this->call('vendor:publish', [
            '--tag'   => 'sns-factories',
            '--force' => true,
        ]);

        $this->comment("复制 stubs ...");
        copy(__DIR__ . '/stubs/Post.stub', app_path('Post.php'));
        copy(__DIR__ . '/stubs/Favorite.stub', app_path('Favorite.php'));
        copy(__DIR__ . '/stubs/PostRecommend.stub', app_path('PostRecommend.php'));

        $this->comment('迁移数据库变化...');
        $this->call('migrate');
    }
}
