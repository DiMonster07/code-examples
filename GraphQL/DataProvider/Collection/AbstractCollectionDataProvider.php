<?php

namespace App\DataProvider\Collection;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Manager\AbstractCriteriaManager;
use Doctrine\ORM\QueryBuilder;

/**
 * Class AbstractCollectionDataProvider.
 */
abstract class AbstractCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    /**
     * @var AbstractCriteriaManager
     */
    protected AbstractCriteriaManager $entityManager;

    /**
     * @var iterable|array
     */
    protected iterable $collectionExtensions;

    /**
     * AbstractCollectionDataProvider constructor.
     *
     * @param AbstractCriteriaManager $entityManager
     * @param iterable                $collectionExtensions
     */
    public function __construct(AbstractCriteriaManager $entityManager, iterable $collectionExtensions = [])
    {
        $this->entityManager        = $entityManager;
        $this->collectionExtensions = $collectionExtensions;
    }

    /**
     * @param string      $resourceClass
     * @param string|null $operationName
     * @param array       $context
     *
     * @return bool
     */
    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return $resourceClass === $this->entityManager->getClass();
    }

    /**
     * {@inheritdoc}
     *
     * @phan-suppress PhanPluginUnknownMethodReturnType
     * @phan-suppress PhanParamTooMany
     */
    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = [])
    {
        $queryBuilder = $this->getQueryBuilder($operationName, $context);

        $queryNameGenerator = new QueryNameGenerator();
        /** @var ContextAwareQueryCollectionExtensionInterface $extension */
        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);

            /* @phpstan-ignore-next-line */
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult($resourceClass, $operationName, $context)) {
                /* @phpstan-ignore-next-line */
                return $extension->getResult($queryBuilder, $resourceClass, $operationName, $context);
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param string|null $operationName
     * @param array       $context
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder(?string $operationName = null, array $context = []): QueryBuilder
    {
        return $this->entityManager->repository()->getSimpleQB();
    }

    /**
     * @return string
     */
    protected function getAlias(): string
    {
        return $this->entityManager->repository()->getAlias();
    }
}
