<?php

namespace App\Repository;

use App\Entity\Spectacle;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SpectacleRepository.
 */
class SpectacleRepository extends AbstractEnabledEntityRepository
{
    /**
     * SpectacleRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Spectacle::class);
    }

    /**
     * {@inheritdoc}
     */
    public function applyCriteria(QueryBuilder $qb, array $criteria): void
    {
        parent::applyCriteria($qb, $criteria);

        $this->applySimpleCriteria($qb, 'kinoplanId', $criteria['kinoplan_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureCriteria(OptionsResolver $resolver): void
    {
        parent::configureCriteria($resolver);

        $resolver->setDefaults([
            'kinoplan_id' => null,
        ]);

        $resolver
            ->setAllowedTypes('kinoplan_id', ['null', 'integer', 'string'])
        ;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return 'spectacle';
    }
}
