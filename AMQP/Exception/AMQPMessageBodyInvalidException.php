<?php

namespace App\AMQP\Exception;

use App\Exception\AbstractNameableException;

/**
 * Class AMQPMessageBodyInvalidException.
 */
class AMQPMessageBodyInvalidException extends AbstractNameableException
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'AMQPMessageBodyInvalid';
    }
}
