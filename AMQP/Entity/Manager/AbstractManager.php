<?php

namespace App\Entity\Manager;

use Doctrine\ORM\ORMException;
use Sonata\Doctrine\Entity\BaseEntityManager;

/**
 * Class AbstractManager.
 */
abstract class AbstractManager extends BaseEntityManager
{
    /**
     * @param object $entity
     *
     * @throws ORMException
     *
     * @return object
     */
    public function merge($entity)
    {
        $this->checkObject($entity);

        return $this->getEntityManager()->merge($entity);
    }

    /**
     * @param string $class
     *
     * @return string mixed
     *
     * @suppress PhanUndeclaredProperty
     * @psalm-suppress DeprecatedClass
     * @psalm-suppress NoInterfaceProperties
     */
    protected function getTableNameByClass($class)
    {
        return $this->getObjectManager()->getClassMetadata($class)->table['name'];
    }
}
