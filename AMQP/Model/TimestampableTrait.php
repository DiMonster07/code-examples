<?php

namespace App\Model;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait TimestampableTrait
{
    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @param DateTime $createdAt
     *
     * @return TimestampableInterface
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $updatedAt
     *
     * @return TimestampableInterface
     */
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist()
     */
    public function timestampablePrePersist()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function timestampablePreUpdate()
    {
        $this->updatedAt = new DateTime();
    }

    /**
     * @return int|null
     *
     * @psalm-suppress DocblockTypeContradiction
     * @psalm-suppress RedundantConditionGivenDocblockType
     */
    public function getCreatedAtTimestamp()
    {
        return $this->createdAt !== null ? $this->createdAt->getTimestamp() : null;
    }

    /**
     * @return int|null
     *
     * @psalm-suppress DocblockTypeContradiction
     * @psalm-suppress RedundantConditionGivenDocblockType
     */
    public function getUpdatedAtTimestamp()
    {
        return $this->updatedAt !== null ? $this->updatedAt->getTimestamp() : null;
    }
}
