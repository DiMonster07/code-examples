<?php

namespace App\Entity\Manager;

use App\Entity\RabbitMQVariables;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class RabbitMQVariablesManager.
 */
class RabbitMQVariablesManager
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var RabbitMQVariables|null
     */
    private $rabbitMQVariables;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return RabbitMQVariables
     */
    public function getRabbitMQVariables(): RabbitMQVariables
    {
        return $this->initRabbitMQVariables();
    }

    /**
     * @return $this
     */
    public function clearRabbitMQVariables(): self
    {
        $this->rabbitMQVariables = null;

        return $this;
    }

    /**
     * @param RabbitMQVariables $rabbitMQVariables
     */
    public function save(RabbitMQVariables $rabbitMQVariables)
    {
        $this->em->persist($rabbitMQVariables);
        $this->em->flush();

        $this->rabbitMQVariables = $rabbitMQVariables;
    }

    /**
     * @return RabbitMQVariables
     */
    private function initRabbitMQVariables(): RabbitMQVariables
    {
        if ($this->rabbitMQVariables === null) {
            $this->rabbitMQVariables = $this->em->getRepository('App:RabbitMQVariables')->findOneBy([]);

            if ($this->rabbitMQVariables === null) {
                $this->rabbitMQVariables = new RabbitMQVariables();

                $this->save($this->rabbitMQVariables);
            }
        }

        return $this->rabbitMQVariables;
    }
}
