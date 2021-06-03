<?php

namespace App\PassportCheckService\Exception;

use App\Exception\AbstractNameableException;

/**
 * Class CurlResponseDecodeFailedException.
 */
class CurlResponseDecodeFailedException extends AbstractNameableException
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'CurlResponseDecodeFailed';
    }
}
