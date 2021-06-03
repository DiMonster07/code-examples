<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * AbstractEnabledEntityRepository.
 */
abstract class AbstractEnabledEntityRepository extends AbstractEntityRepository
{
    public const ENABLED_FIELD_NAME                    = 'enabled';
    public const IGNORE_ENABLED_CRITERIA_NAME          = 'ignore_enabled';
    public const IGNORE_ENABLED_CRITERIA_DEFAULT_VALUE = false;

    /**
     * @return string
     */
    protected function getEnabledFieldName(): string
    {
        return self::ENABLED_FIELD_NAME;
    }

    /**
     * @param QueryBuilder $qb
     * @param array        $criteria
     */
    protected function applyEnabledCriteria(QueryBuilder $qb, array $criteria)
    {
        if ($criteria[self::IGNORE_ENABLED_CRITERIA_NAME] === true) {
            return;
        }

        $qb
            ->andWhere("{$this->getAlias()}.{$this->getEnabledFieldName()} = 1")
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureCriteria(OptionsResolver $resolver): void
    {
        parent::configureCriteria($resolver);

        $resolver
            ->setDefault(self::IGNORE_ENABLED_CRITERIA_NAME, self::IGNORE_ENABLED_CRITERIA_DEFAULT_VALUE)
            ->setAllowedTypes(self::IGNORE_ENABLED_CRITERIA_NAME, ['boolean'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function applyCriteria(QueryBuilder $qb, array $criteria): void
    {
        parent::applyCriteria($qb, $criteria);

        $this->applyEnabledCriteria($qb, $criteria);
    }
}
