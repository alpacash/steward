<?php

namespace App\Exceptions;

/**
 * Class DependenciesMissingException
 * @package App\Exceptions
 */
class DependenciesMissingException extends \Exception
{
    /**
     * @var string|null
     */
    protected $installScript;

    /**
     * @var array
     */
    protected $missing;

    /**
     * DependenciesMissingException constructor.
     *
     * @param array $missing
     */
    public function __construct(array $missing)
    {
        parent::__construct("Dependencies missing: " . implode(", ", array_keys($missing)));

        $this->missing = $missing;
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return $this->missing;
    }
}
