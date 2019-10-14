<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class SourceSymlinkVendor extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'source:symlink-vendor {site}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Symlink vendor packages to local copies.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

    }
}
