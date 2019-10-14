<?php

namespace App\Commands;

use App\Service\Http\Secure;
use LaravelZero\Framework\Commands\Command;

class HttpSecure extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'http:secure {domain}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Secure http site with an SSL certificate.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        Secure::domain($this->argument('domain'))->secure();
    }
}
