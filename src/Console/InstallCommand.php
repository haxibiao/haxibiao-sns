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
    protected $signature = 'sns:install {--force}';

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
        $force = $this->option('force');
        $this->comment("复制 stubs ...");
        copyStubs(__DIR__, $force);

        //FIXME: 重构到 sns:publish
        $this->info('发布资源');
        $this->call('vendor:publish', [
            '--tag'   => 'sns-config',
            '--force' => true,
        ]);
        $this->call('vendor:publish', [
            '--tag'   => 'sns-graphql',
            '--force' => true,
        ]);

        // $this->call('vendor:publish', [
        //     '--tag'   => 'sns-tests',
        //     '--force' => true,
        // ]);
    }
}
