<?php

namespace App\PassportCheckService\Exception;

use App\Exception\AbstractNameableException;

/**
 * Class NotValidPersonalDataException.
 */
class NotValidPersonalDataException extends AbstractNameableException
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'NotValidPersonalData';
    }
}
