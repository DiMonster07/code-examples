<?php

namespace App\AMQP\Exception;

use App\Exception\AbstractNameableException;

/**
 * Class AMQPMessageDecodeFailedException.
 */
class AMQPMessageDecodeFailedException extends AbstractNameableException
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'AMQPMessageDecodeFailed';
    }
}
