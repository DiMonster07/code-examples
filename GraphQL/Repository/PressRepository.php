<?php

namespace App\Repository;

use App\Entity\Press;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class PressRepository.
 */
class PressRepository extends AbstractEnabledEntityRepository
{
    /**
     * PressRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Press::class);
    }

    /**
     * @param array $criteria
     *
     * @return QueryBuilder
     */
    public function getLatestEventsQB(array $criteria = []): QueryBuilder
    {
        return $this->getAllQB($criteria)
            ->addOrderBy("{$this->getAlias()}.publishedAt", self::ORDER_DESCENDING)
        ;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return 'press';
    }
}
