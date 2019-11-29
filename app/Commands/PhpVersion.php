<?php

namespace App\Commands;

use App\Exceptions\InvalidServerVersionException;

class PhpVersion extends StackCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'php:version {version}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Switch to another php version';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function process(): int
    {
        $server = $this->stack->phpServer();

        try {
            $version = $this->argument('version');
            $this->output->note("Attempting to switch PHP: {$server->version()} => {$version}");
            $server->useVersion($version);
        } catch (InvalidServerVersionException $e) {
            return $this->fail($e->getMessage());
        }

        return $this->success("Done! New PHP version is {$server->version()}");
    }
}
