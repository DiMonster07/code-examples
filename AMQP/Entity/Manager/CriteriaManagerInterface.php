<?php

namespace App\Entity\Manager;

use Doctrine\ORM\QueryBuilder;

interface CriteriaManagerInterface
{
    /**
     * @param array $criteria
     *
     * @return object|null
     */
    public function getOne(array $criteria = []);

    /**
     * @param array $criteria
     *
     * @return object[]
     */
    public function getAll(array $criteria = []);

    /**
     * @param array $criteria
     *
     * @return int
     */
    public function getCount(array $criteria = []);

    /**
     * Get all entity query builder.
     *
     * @param array $criteria
     *
     * @return QueryBuilder
     *
     * @internal param array $criteria
     */
    public function getAllQB(array $criteria = []);

    /**
     * @param QueryBuilder $qb
     * @param array        $criteria
     *
     * @psalm-suppress MissingReturnType
     */
    public function applyCriteria(QueryBuilder $qb, array $criteria);
}
