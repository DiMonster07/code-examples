<?php

namespace App\DataProvider\Collection;

use App\Manager\EventManager;
use App\Resolver\Query\Operation\AbstractOperationType;
use Doctrine\ORM\QueryBuilder;

/**
 * Class EventCollectionDataProvider.
 *
 * @property EventManager $entityManager
 */
class EventCollectionDataProvider extends AbstractCollectionDataProvider
{
    /**
     * {@inheritdoc}
     */
    protected function getQueryBuilder(?string $operationName = null, array $context = []): QueryBuilder
    {
        if ($operationName === AbstractOperationType::COLLECTION_QUERY) {
            return $this->entityManager->repository()->getAllQB();
        }

        return parent::getQueryBuilder($operationName);
    }
}
