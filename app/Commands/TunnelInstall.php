<?php

namespace App\Commands;

use App\LocalTunnel;
use LaravelZero\Framework\Commands\Command;

class TunnelInstall extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'tunnel:install';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Install localtunnel from source.';

    /**
     * @var \App\LocalTunnel
     */
    protected $tunnel;

    /**
     * TunnelInstall constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->tunnel = new LocalTunnel();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->output->note("Installing localtunnel from source...");

        if ($this->tunnel->installed()
            && ! $this->confirm("Source code seems present. Would you like to rebuild or reinstall?")) {
            return 0;
        }

        try {
            if ($this->tunnel->install() === 0) {
                $this->output->success("Localtunnel was installed successfully. Now run http:expose to use it!");

                return 0;
            }

            throw new \Exception("Could not successfully install localtunnel");
        } catch (\Exception $e) {
            $this->output->error($e->getMessage());

            return 1;
        }
    }
}
