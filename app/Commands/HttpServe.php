<?php

namespace App\Commands;

use App\Shell;
use LaravelZero\Framework\Commands\Command;

class HttpServe extends Command
{

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'http:serve {--p|port=8080} {--d|document-root=} {--tmux} {--D|daemonize} {--expose}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Serve a specific document root using php built-in web server';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $docRoot = $this->option('document-root') ?: getcwd();
        $port = $this->option('port');

        if ($this->option('tmux') || $this->option('daemonize') || $this->option('expose')) {
            global $argv;
            $daemon = $this->option('expose') || $this->option('daemonize') ? ' -d ' : '';

            if (! empty($daemon)) {
                $this->output->success("Attach to the session using 'tmux attach -t expose'");
            }

            $argv[1] = 'http:serve';

            passthru("tmux new {$daemon} -s serve '"
                . implode(' ', array_diff($argv, ['--tmux', '-D', '--expose'])) . '\'');

            if ($this->option('expose')) {
                $daemon = $this->option('daemonize') ? ' -d ' : '';
                passthru("tmux new {$daemon} -s expose 'stew http:expose localhost --port {$port}'");

                Shell::cmd("tmux kill-session -t serve");
            }

            return 0;
        }

        if (! is_numeric($port) || $port < 1024) {
            $this->output->error("Port {$port} is invalid, must be numeric and 1024 or higher.");

            return 1;
        }

        $this->output->note("Serving document root {$docRoot}");
        $this->output->writeln("  <fg=cyan>-></> <options=underscore>http://127.0.0.1:{$port}</>\n");

        return Shell::cmd("php -t {$docRoot} -S 127.0.0.1:" . $port, $this->output);
    }
}
