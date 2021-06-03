<?php

namespace App\DataProvider\Collection;

use App\Manager\PressManager;
use App\Resolver\Query\Operation\PressOperationType;
use Doctrine\ORM\QueryBuilder;

/**
 * Class PressCollectionDataProvider.
 *
 * @property PressManager $entityManager
 */
class PressCollectionDataProvider extends AbstractCollectionDataProvider
{
    /**
     * {@inheritdoc}
     */
    protected function getQueryBuilder(?string $operationName = null, array $context = []): QueryBuilder
    {
        if ($operationName === PressOperationType::LATEST_OF_MAIN_PAGE_QUERY) {
            return $this->entityManager->getLatestEventsQBForMainPage();
        }

        return parent::getQueryBuilder($operationName);
    }
}
