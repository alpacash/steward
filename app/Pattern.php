<?php

namespace App;

class Pattern
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * SearchPattern constructor.
     *
     * @param string $pattern
     */
    public function __construct(
        string $pattern
    ) {
        $this->pattern = $pattern;
    }

    /**
     * @param string $pattern
     *
     * @return \self
     */
    public static function create(string $pattern)
    {
        return new static($pattern);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "/^{$this->pattern}/im";
    }
}
