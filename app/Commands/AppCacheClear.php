<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Facades\Cache;

class AppCacheClear extends Command
{

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'app:cache-clear';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Clear the steward application caches';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Cache::flush();

        $this->output->success("Application caches have been cleared!");

        return 1;
    }
}
