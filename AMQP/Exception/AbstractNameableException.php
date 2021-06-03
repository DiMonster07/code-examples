<?php

namespace App\Exception;

use Exception;

/**
 * Class AbstractNameableException.
 */
abstract class AbstractNameableException extends Exception
{
    /**
     * AbstractNameableException constructor.
     */
    public function __construct()
    {
        parent::__construct($this->getName());
    }

    /**
     * @return string
     */
    abstract public function getName(): string;
}
