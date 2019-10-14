<?php

namespace App\Commands;

use App\Service\Http\Secure;
use LaravelZero\Framework\Commands\Command;

class HttpUnsecure extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'http:unsecure {domain}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Remove a sites self-signed certificate.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $domainSecurity = Secure::domain($this->argument('domain'));

        if (! $domainSecurity->isSecure()) {
            $this->output->error("The domain is not secure and thus can not be unsecured.");

            return;
        }

        $domainSecurity->unsecure();

        $this->output->success("The domain was unsecured successfully. All files were removed.");
    }
}
