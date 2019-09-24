<?php

namespace App\Commands;

use App\Exceptions\ConfigFileException;
use App\Exceptions\DependenciesMissingException;
use App\Shell;
use App\Stack;
use LaravelZero\Framework\Commands\Command;

abstract class StackCommand extends Command
{
    /**
     * @var \App\Stack
     */
    protected $stack;

    /**
     * @param string $message
     *
     * @return int
     */
    protected function success(string $message = "")
    {
        if (!empty($message)) {
            $this->output->success($message);
        }

        return 0;
    }

    /**
     * @param string $message
     *
     * @return int
     */
    protected function fail(string $message = "")
    {
        if (!empty($message)) {
            $this->output->error($message);
        }

        return 1;
    }

    /**
     * @return int
     */
    public function handle()
    {
        try {
            $this->stack = Stack::compose()->verify();

            return $this->process();
        } catch (DependenciesMissingException $e) {
            foreach ($e->getDependencies() as $dependency => $script) {

                if (empty($script)) {
                    continue;
                }

                if (! $this->output->confirm("Dependency {$dependency} is missing."
                    . " Would you like to install it now?")
                    || Shell::cmd($script, $this->output) > 0) {
                    $final = true; break;
                }
            }

            if (isset($final)) {
                return $this->fail($e->getMessage());
            }

            return $this->handle();
        } catch (ConfigFileException $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @return int
     */
    protected function status()
    {
        return $this->success(trim(shell_exec("stew -V"))
            . " // [Php]: " . $this->stack->phpServer()->version()
            . " // [Caddy]: " . $this->stack->httpServer()->version()
            . " // [Dnsmasq]: " . $this->stack->dnsServer()->version());
    }

    /**
     * @return int
     */
    abstract public function process(): int;
}
