<?php

namespace App\DataProvider\Collection;

use App\Entity\Session;
use App\Manager\SessionManager;
use App\Resolver\Query\Operation\SessionOperationType;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SessionCollectionDataProvider.
 *
 * @property SessionManager $entityManager
 */
class SessionCollectionDataProvider extends AbstractCollectionDataProvider
{
    /**
     * {@inheritdoc}
     *
     * @phan-suppress PhanPluginUnknownMethodReturnType
     * @phan-suppress PhanParamTooMany
     */
    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = [])
    {
        if ($operationName === SessionOperationType::MAIN_BLOCK_OF_MAIN_PAGE_QUERY) {
            return $this->getCollectionForMainBlockOfMainPage();
        }

        return parent::getCollection($resourceClass, $operationName, $context);
    }

    /**
     * {@inheritdoc}
     */
    protected function getQueryBuilder(?string $operationName = null, array $context = []): QueryBuilder
    {
        if ($operationName === SessionOperationType::PLAYBILL_BLOCK_QUERY) {
            if (!isset($context['filters']['kinoplanSessionDate'])) {
                return $this->entityManager->repository()->getPlaybillBlockSessionsQB();
            }
        }

        return parent::getQueryBuilder();
    }

    /**
     * @return Session[]|array|iterable
     */
    private function getCollectionForMainBlockOfMainPage()
    {
        return $this->entityManager->getGroupedByUniqueSpectacleAndShowAtMainPageField();
    }
}
