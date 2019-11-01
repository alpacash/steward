<?php

namespace App\Commands;

use App\Shell;
use LaravelZero\Framework\Commands\Command;

class ClipboardGitBranch extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'clipboard:git-branch {--print}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Copy the current git branch name to your clipboard';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $branch = trim(shell_exec("echo \"$(git branch | grep \* | cut -d ' ' -f2)\""));

        if ($this->option('print')) {
            $this->output->writeln(!empty($branch) ? $branch : '');

            return 0;
        }

        if (! empty($branch) && Shell::cmd("printf \"{$branch}\" | pbcopy") === 0) {
            $this->output->success("Git branch name {$branch} successfully copied to the clipboard!");

            return 0;
        }

        $this->output->error("Something went wrong while copying your ssh key.");

        return 1;
    }
}
