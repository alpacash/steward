<?php

namespace App;

class PhpExtension
{
    const KNOWN_ZEND_EXTENSIONS = ['xdebug'];
    const ZEND_EXTENSION = 'zend_extension';
    const EXTENSION = 'extension';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var \App\PhpConfig
     */
    protected $phpConfig;

    /**
     * @var string
     */
    protected $suffix;

    /**
     * @var \App\PhpServer
     */
    protected $server;

    /**
     * PhpExtension constructor.
     *
     * @param \App\PhpServer $server
     * @param string         $name
     * @param string         $type
     * @param string         $suffix
     *
     * @throws \Exception
     */
    public function __construct(
        PhpServer $server,
        string $name,
        string $type = self::EXTENSION,
        string $suffix = "so"
    ) {
        if (in_array($name, self::KNOWN_ZEND_EXTENSIONS)) {
            $type = self::ZEND_EXTENSION;
        }

        if (!in_array($type, [self::ZEND_EXTENSION, self::EXTENSION])) {
            throw new \Exception("Extension type must be one of extension, zend_extension");
        }

        $this->name = $name;
        $this->type = $type;
        $this->suffix = $suffix;
        $this->server = $server;
        $this->phpConfig = $this->server->config();
    }

    /**
     * @return self
     */
    public function disable()
    {
        $this->phpConfig->replace(
            Pattern::create($this->iniEnabled()),
            $this->iniDisabled()
        )->save();

        return $this;
    }

    /**
     * @return self
     */
    public function enable()
    {
        $this->phpConfig->replace(
            Pattern::create($this->iniDisabled()),
            $this->iniEnabled()
        )->save();

        return $this;
    }

    /**
     * @return bool
     */
    public function enabled()
    {
        return $this->phpConfig->has(Pattern::create($this->iniEnabled()));
    }

    /**
     * @return bool
     */
    public function disabled()
    {
        return ! $this->enabled();
    }

    /**
     * @return string
     */
    protected function iniDisabled()
    {
        return ";{$this->iniEnabled()}";
    }

    /**
     * @return string
     */
    protected function iniEnabled()
    {
        return "{$this->type}=\"{$this->name}{$this->suffix}\"";
    }
}
