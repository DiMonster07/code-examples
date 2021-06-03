<?php

namespace App\AMQP\Producer;

use App\AMQP\Exception\BuildMessageFailedException;
use App\Entity\PersonalData;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

/**
 * Class PassportCheckProducer.
 */
class PassportCheckProducer extends Producer
{
    /**
     * @param PersonalData $personalData
     * @param int          $priority
     *
     * @throws BuildMessageFailedException
     *
     * @return $this
     */
    public function pushToQueue(PersonalData $personalData, int $priority): self
    {
        $message = $this->buildMessage($personalData);

        $this->publish($message, '', [
            'priority' => $priority,
        ]);

        return $this;
    }

    /**
     * @param PersonalData $personalData
     *
     * @throws BuildMessageFailedException
     *
     * @return string
     */
    private function buildMessage(PersonalData $personalData): string
    {
        $messageData = [
            'personal_data_id' => $personalData->getId(),
            'born_timestamp'   => time(),
        ];

        $message = json_encode($messageData);
        if ($message === false) {
            throw new BuildMessageFailedException();
        }

        return $message;
    }
}
