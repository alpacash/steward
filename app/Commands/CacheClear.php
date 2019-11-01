<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class CacheClear extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'site:clear-cache';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Clear application caches.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $directories = [
            'generated/code',
            'generated/metadata',
            'pub/static',
            'var/cache',
            'var/composer_home',
            'var/page_cache',
            'var/view_preprocessed',
        ];

        foreach ($directories as $directory) {
            $directory = getcwd() . '/' . $directory;
            if (!file_exists($directory)) {
                continue;
            }

            $this->comment("Deleting contents of $directory");
            echo exec("rm -rf $directory/*");
        }

        $this->comment("Clearing redis through redis-cli flushall");
        @exec('redis-cli flushall');
    }
}
