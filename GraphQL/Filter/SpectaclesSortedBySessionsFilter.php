<?php

namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\View\ViewSpectaclesSortedBySessions;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SpectaclesSortedBySessionsFilter.
 */
class SpectaclesSortedBySessionsFilter extends AbstractContextAwareFilter
{
    private const ARGUMENT_NAME = 'sortedBySessions';

    /**
     * @param string $resourceClass
     *
     * @return array
     */
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description[$property] = [
                'property' => $property,
                'type'     => 'bool',
                'required' => false,
            ];
        }

        return $description;
    }

    /**
     * {@inheritdoc}
     *
     * @phan-suppress PhanPluginUnknownMethodParamType
     */
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?string $operationName = null
    ): void {
        if ($property !== self::ARGUMENT_NAME) {
            return;
        }

        if (!$value) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->join(ViewSpectaclesSortedBySessions::class, 'sortedSpectacle', Join::WITH, "{$alias}.id = sortedSpectacle.id")
            ->addOrderBy('sortedSpectacle.orderNumber')
        ;
    }
}
