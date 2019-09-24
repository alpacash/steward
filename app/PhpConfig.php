<?php

namespace App;

use App\Contract\ConfigContract;
use App\Exceptions\ConfigFileCorruptException;
use App\Exceptions\ConfigFileMissingException;

class PhpConfig implements ConfigContract
{

    /**
     * @var string|null
     */
    protected $file;

    /**
     * @var string
     */
    protected $contents;

    /**
     * @var \App\PhpServer
     */
    protected $server;

    /**
     * @param \App\PhpServer $server
     */
    public function __construct(PhpServer $server)
    {
        $this->server = $server;
        $this->file = $server->iniFile();
        $this->contents = \file_get_contents($this->file);
    }

    /**
     * @return self
     *
     * @throws \App\Exceptions\ConfigFileCorruptException
     * @throws \App\Exceptions\ConfigFileMissingException
     */
    public function verify()
    {
        if (!file_exists($this->file)) {
            throw new ConfigFileMissingException($this->file);
        }

        if (empty($this->contents)) {
            throw new ConfigFileCorruptException($this->file);
        }

        return $this;
    }

    /**
     * Write the file to disk.
     *
     * @return self
     */
    public function save(): ConfigContract
    {
        file_put_contents($this->file, $this->contents);

        return $this;
    }

    /**
     * @param string $search
     * @param string $replace
     *
     * @return self
     */
    public function replace(string $search, string $replace)
    {
        if (!$this->has($search)) {
            $this->contents = "{$replace}\n\n{$this->contents}";

            return $this;
        }

        $this->contents = preg_replace(addslashes($search), addslashes($replace), $this->contents);

        return $this;
    }

    /**
     * @param string $pattern
     *
     * @return bool
     */
    public function has(string $pattern)
    {
        return (bool)preg_match($pattern, $this->contents());
    }

    /**
     * @return string
     */
    public function contents()
    {
        return $this->contents;
    }

    public function raw()
    {
        return $this->contents;
    }

    public function matches(string $key, string $value)
    {
        // TODO: Implement matches() method.
    }

    public function get(string $key)
    {
        // TODO: Implement get() method.
    }

    public function set(string $key, string $value)
    {
        // TODO: Implement set() method.
    }
}
