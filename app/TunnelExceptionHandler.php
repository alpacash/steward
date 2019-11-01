<?php

namespace App;

use Illuminate\Console\OutputStyle;

class TunnelExceptionHandler
{
    /**
     * @var \Illuminate\Console\OutputStyle
     */
    protected $output;

    /**
     * TunnelErrorHandler constructor.
     *
     * @param \Illuminate\Console\OutputStyle $output
     */
    public function __construct(
        OutputStyle $output
    ) {
        $this->output = $output;
    }

    /**
     * @param \Exception $exception
     */
    public function __invoke(\Exception $exception)
    {
//        $this->output->error("Http exception");
        $this->output->error($exception . " on " . $exception->getFile() . ":" . $exception->getLine());
    }
}
