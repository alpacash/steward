<?php declare(ticks=1);

namespace App\Commands;

use App\LocalTunnel;
use LaravelZero\Framework\Commands\Command;
use League\CLImate\CLImate;

class HttpExpose extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'http:expose {site} {--p|port=80} {--s|handle=} '
        . '{--J|jail-redirects} {--tmux} {--d|daemonize}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Expose your local site through a random public url.';

    /**
     * @var \App\LocalTunnel
     */
    protected $tunnel;

    /**
     * @var \League\CLImate\CLImate
     */
    protected $cli;

    /**
     * HttpExpose constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->cli = new CLImate();
        $this->tunnel = new LocalTunnel();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('tmux')) {
            global $argv;
            $daemon = $this->option('daemonize') ? ' -d ' : '';

            if (! empty($daemon)) {
                $this->output->success("Attach to the session using 'tmux attach -t expose'");
            }

            passthru("tmux new {$daemon} -s expose '" . implode(' ', array_diff($argv, ['--tmux', '-d'])) . '\'');

            return 0;
        }

        $this->cli->clear();

        $this->output->title('Stew.sh http tunnel - version ' . config('app.version'));
        $this->output->writeln("<comment> ! ⏳ Connecting to the upstream server, please wait...</>");

        if (! $this->tunnel->installed()) {
            $this->output->error("Localtunnel is not installed. Please run tunnel:install to download the source.");

            return 1;
        }

        if (gethostbyname($site = $this->argument('site')) !== '127.0.0.1') {
            $this->output->error("{$site} must point to 127.0.0.1, "
                . "use stack:status to verify that dnsmasq is running");

            return 1;
        }

        $this->tunnel->start(
            $this->argument('site'),
            $this->option('port'),
            $this->option('jail-redirects'),
            $this->option('handle'),
            $this->output
        );

        return 0;
    }
}
