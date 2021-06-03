<?php

namespace App\Repository;

use App\Entity\Actor;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ActorRepository.
 */
class ActorRepository extends AbstractEnabledEntityRepository
{
    /**
     * ActorRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Actor::class);
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return 'actor';
    }
}
