<?php

namespace App\Model;

use Doctrine\ORM\QueryBuilder;

/**
 * Trait EntityRepositoryTrait
 */
trait EntityRepositoryTrait
{
    /**
     * @param ResourceInterface $resource
     *
     * @suppress PhanUndeclaredProperty
     */
    public function traitAdd(ResourceInterface $resource)
    {
        $this->_em->persist($resource);
        $this->_em->flush();
    }

    /**
     * @param ResourceInterface $resource
     *
     * @suppress PhanUndeclaredProperty
     */
    public function traitRemove(ResourceInterface $resource)
    {
        if ($this->find($resource->getId()) !== null) {
            $this->_em->remove($resource);
            $this->_em->flush();
        }
    }

    /**
     * @return string
     */
    public function traitGetAlias()
    {
        return strtolower($this->getClassMetadata()->getReflectionClass()->getShortName());
    }

    /**
     * @return QueryBuilder
     */
    public function traitGetSimpleQB()
    {
        return $this->getSimpleQBWithAlias($this->getAlias());
    }

    /**
     * @return QueryBuilder
     */
    public function traitGetCountQueryBuilder()
    {
        return $this->traitCreateCountQueryBuilder($this->getAlias());
    }

    /**
     * @param string $alias
     *
     * @return QueryBuilder
     */
    protected function traitGetSimpleQBWithAlias($alias)
    {
        return $this->createQueryBuilder($alias);
    }

    /**
     * Creates a new QueryBuilder instance for COUNT query.
     *
     * @param string      $alias
     * @param string|null $indexBy
     *
     * @return QueryBuilder
     *
     * @suppress PhanUndeclaredProperty
     */
    protected function traitCreateCountQueryBuilder($alias, $indexBy = null)
    {
        return $this->_em
            ->createQueryBuilder()
            ->select("COUNT({$alias}.id)")
            ->from($this->_entityName, $alias, $indexBy)
        ;
    }
}
