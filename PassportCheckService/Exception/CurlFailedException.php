<?php

namespace App\PassportCheckService\Exception;

use App\Exception\AbstractNameableException;

/**
 * Class CurlFailedException.
 */
class CurlFailedException extends AbstractNameableException
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'CurlFailed';
    }
}
