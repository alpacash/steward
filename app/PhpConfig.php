<?php

namespace App;

class PhpConfig
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
     *
     * @throws \Exception
     */
    public function __construct(PhpServer $server) {
        $this->server = $server;
        $this->file = $server->iniFile();

        if (!file_exists($this->file)) {
            throw new \Exception("File does not exist: {$this->file}");
        }

        $this->contents = \file_get_contents($this->file);

        if (empty($this->contents)) {
            throw new \Exception("File seems corrupt: {$this->file}");
        }
    }

    /**
     * Write the file to disk.
     *
     * @return self
     */
    public function write()
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
        $this->contents  = preg_replace($search, $replace, $this->contents);

        return $this;
    }

    /**
     * @param string $pattern
     *
     * @return bool
     */
    public function has(string $pattern)
    {
        return (bool) preg_match($pattern, $this->contents());
    }

    /**
     * @return string
     */
    public function contents()
    {
        return $this->contents;
    }
}
