<?php

namespace App\Entity\Repository;

use App\Model\EntityRepositoryTrait;
use Doctrine\ORM\EntityRepository as BaseEntityRepository;

/**
 * EntityRepository.
 */
class EntityRepository extends BaseEntityRepository implements RepositoryInterface
{
    use EntityRepositoryTrait {
        EntityRepositoryTrait::traitAdd as public add;
        EntityRepositoryTrait::traitGetAlias as public getAlias;
        EntityRepositoryTrait::traitGetSimpleQB as public getSimpleQB;
        EntityRepositoryTrait::traitRemove as public remove;
        EntityRepositoryTrait::traitGetCountQueryBuilder as public getCountQueryBuilder;
        EntityRepositoryTrait::traitGetSimpleQBWithAlias as protected getSimpleQBWithAlias;
        EntityRepositoryTrait::traitCreateCountQueryBuilder as protected createCountQueryBuilder;
    }
}
