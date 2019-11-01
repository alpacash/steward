<?php

namespace App\Commands;

use App\Shell;
use LaravelZero\Framework\Commands\Command;

class ClipboardSshKey extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'clipboard:ssh-key {file=id_rsa.pub}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Copy your public ssh key to the clipboard';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $file = $_SERVER['HOME'] . '/.ssh/' . $this->argument('file');

        if (! file_exists($file)) {
            $this->output->error("File does not exist: $file");

            return 1;
        }

        if (Shell::cmd("cat $file | pbcopy") === 0) {
            $this->output->success('Your ssh public key was successfully copied to the clipboard!');

            return 0;
        }

        $this->output->error("Something went wrong while copying your ssh key.");

        return 1;
    }
}
