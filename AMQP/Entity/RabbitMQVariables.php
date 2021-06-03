<?php

namespace App\Entity;

use App\Model\TimestampableInterface;
use App\Model\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Model\ResourceInterface;
use App\Model\ResourceTrait;

/**
 * Class RabbitMQVariables.
 *
 * @ORM\Table(name="rabbitmq_variables")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class RabbitMQVariables implements ResourceInterface, TimestampableInterface
{
    use ResourceTrait;
    use TimestampableTrait;

    /**
     * @var int|null
     *
     * @ORM\Column(name="passport_check_last_request_sent_at_timestamp", type="integer", nullable=true)
     */
    private $passportCheckLastRequestSentAtTimestamp;

    /**
     * @var bool
     *
     * @ORM\Column(name="passport_check_capture_required", type="boolean", options={"default" = false})
     */
    private $passportCheckCaptureRequired = false;

    /**
     * @param int|null $passportCheckLastRequestSentAtTimestamp
     *
     * @return $this
     */
    public function setPassportCheckLastRequestSentAtTimestamp(?int $passportCheckLastRequestSentAtTimestamp): self
    {
        $this->passportCheckLastRequestSentAtTimestamp = $passportCheckLastRequestSentAtTimestamp;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPassportCheckLastRequestSentAtTimestamp(): ?int
    {
        return $this->passportCheckLastRequestSentAtTimestamp;
    }

    /**
     * @param bool $passportCheckCaptureRequired
     *
     * @return RabbitMQVariables
     */
    public function setPassportCheckCaptureRequired(bool $passportCheckCaptureRequired): self
    {
        $this->passportCheckCaptureRequired = $passportCheckCaptureRequired;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPassportCheckCaptureRequired(): bool
    {
        return $this->passportCheckCaptureRequired;
    }
}
