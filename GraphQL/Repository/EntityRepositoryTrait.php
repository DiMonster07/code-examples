<?php

namespace App\Repository;

use App\Model\ResourceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;

/**
 * Trait EntityRepositoryTrait.
 *
 * @method ClassMetadata getClassMetadata()
 *
 * @property EntityManagerInterface $_em
 */
trait EntityRepositoryTrait
{
    /**
     * @param ResourceInterface $resource
     *
     * @suppress PhanUndeclaredProperty
     */
    public function traitAdd(ResourceInterface $resource): void
    {
        $this->_em->persist($resource);
        $this->_em->flush();
    }

    /**
     * @param ResourceInterface $resource
     *
     * @suppress PhanUndeclaredProperty
     */
    public function traitRemove(ResourceInterface $resource): void
    {
        if ($this->find($resource->getId()) !== null) {
            $this->_em->remove($resource);
            $this->_em->flush();
        }
    }

    /**
     * @return string
     */
    public function traitGetAlias(): string
    {
        return strtolower($this->getClassMetadata()->getReflectionClass()->getShortName());
    }

    /**
     * @return QueryBuilder
     */
    public function traitGetSimpleQB(): QueryBuilder
    {
        return $this->getSimpleQBWithAlias($this->getAlias());
    }

    /**
     * @param string $alias
     *
     * @return QueryBuilder
     */
    public function traitGetSimpleQBWithAlias(string $alias): QueryBuilder
    {
        return $this
            ->createQueryBuilder($alias)
        ;
    }
}
