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
        $this->comment("复制 sns stubs ...");
        copyStubs(__DIR__, $force);

        $this->info('发布 sns 资源');
        $this->call('sns:publish');

        $this->info("迁移 sns 数据库");
        $this->call('migrate');
    }
}
