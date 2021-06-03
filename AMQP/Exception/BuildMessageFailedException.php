<?php

namespace App\AMQP\Exception;

use App\Exception\AbstractNameableException;

/**
 * Class BuildMessageFailedException.
 */
class BuildMessageFailedException extends AbstractNameableException
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'BuildMessageFailed';
    }
}
