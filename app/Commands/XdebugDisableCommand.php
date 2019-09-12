<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class XdebugDisableCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'xdebug:disable';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Disable xdebug';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $fileName = "/usr/local/etc/php/7.1/php.ini";
        $pattern = "/^zend_extension=\"xdebug.so\";*/im";
        $fileContent = \file_get_contents($fileName);

        if (!preg_match($pattern, $fileContent)) {
            $this->output->comment("Xdebug is already disabled!");

            return;
        }

        $fileContent = preg_replace(
            $pattern,
            ";zend_extension=\"xdebug.so\";",
            $fileContent
        );

        \file_put_contents($fileName, $fileContent);

        exec("brew services restart php");

        $this->output->success("Done!");
    }
}
