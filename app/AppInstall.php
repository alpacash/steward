<?php

namespace App;

use Illuminate\Console\OutputStyle;

class AppInstall
{

    /**
     * @var \Illuminate\Console\OutputStyle
     */
    protected $output;

    /**
     * AppInstall constructor.
     *
     * @param \Illuminate\Console\OutputStyle $output
     */
    public function __construct(
        OutputStyle $output
    ) {
        $this->output = $output;
    }

    /**
     * @return bool
     */
    public function execute()
    {
        try {
            return $this->download();
        } catch (\Exception $e) {
            $this->output->error($e->getMessage());

            return false;
        }
    }

    /**
     * @return bool
     */
    protected function download()
    {
        $command = 'curl -s --progress-bar https://stew.sh/install | ';
        $suffix = 'bash';
        if (! is_writable("/usr/local/bin")) {
            $this->output->warning("/usr/local/bin is not writable. Sudo password required!");
            $suffix = 'sudo ' . $suffix;
        }

        return Shell::cmd($command . $suffix, $this->output) === 0;
    }
}
