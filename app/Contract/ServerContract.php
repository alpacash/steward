<?php

namespace App\Contract;

interface ServerContract
{
    /**
     * @return self
     */
    public function restart();

    /**
     * @return self
     */
    public function stop();

    /**
     * @return string
     */
    public function version(): string;

    /**
     * @return \App\Contract\ConfigContract
     */
    public function config(): ConfigContract;

    /**
     * @return string
     */
    public function label(): string;
}
