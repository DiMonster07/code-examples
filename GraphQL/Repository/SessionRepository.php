<?php

namespace App\Repository;

use App\Entity\Session;
use App\Entity\View\ViewPlaybillSessions;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SessionRepository.
 */
class SessionRepository extends AbstractEnabledEntityRepository
{
    /**
     * SessionRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    /**
     * @return QueryBuilder
     */
    public function getPlaybillBlockSessionsQB(): QueryBuilder
    {
        return $this->getAllQB()
            ->join(ViewPlaybillSessions::class, 'playbillSession', Join::WITH, "{$this->getAlias()}.id = playbillSession.id")
        ;
    }

    /**
     * @param array $criteria
     *
     * @return QueryBuilder
     */
    public function getFutureSessionsQB(array $criteria = []): QueryBuilder
    {
        return $this->getAllQB($criteria)
            ->andWhere("{$this->getAlias()}.kinoplanSessionDate > CURRENT_TIMESTAMP()")
        ;
    }

    /**
     * @param array $criteria
     * @param bool  $future
     *
     * @return QueryBuilder
     */
    public function getSessionsJoinSpectaclesQB(array $criteria = [], bool $future = true): QueryBuilder
    {
        $alias = $this->getAlias();

        $qb = $this->getAllQB($criteria)
            ->leftJoin("{$alias}.spectacle", 'spectacle')
            ->orderBy("{$alias}.kinoplanSessionDate", $future ? self::ORDER_ASCENDING : self::ORDER_DESCENDING)
        ;

        if (!$future) {
            return $qb->andWhere("{$alias}.kinoplanSessionDate < CURRENT_TIMESTAMP()");
        }

        return $qb->andWhere("{$alias}.kinoplanSessionDate > CURRENT_TIMESTAMP()");
    }

    /**
     * @return QueryBuilder
     */
    public function getSimpleQB(): QueryBuilder
    {
        return parent::getSimpleQB()
            ->addOrderBy("{$this->getAlias()}.kinoplanSessionDate")
        ;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return 'session';
    }

    /**
     * {@inheritdoc}
     */
    protected function applyCriteria(QueryBuilder $qb, array $criteria): void
    {
        parent::applyCriteria($qb, $criteria);

        $this->applySimpleCriteria($qb, 'kinoplanId', $criteria['kinoplan_id']);
        $this->applySimpleCriteria($qb, 'showAtMainPage', $criteria['show_at_main_page']);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureCriteria(OptionsResolver $resolver): void
    {
        parent::configureCriteria($resolver);

        $resolver->setDefaults([
            'kinoplan_id'       => null,
            'show_at_main_page' => null,
        ]);

        $resolver
            ->setAllowedTypes('kinoplan_id', ['null', 'integer', 'string'])
            ->setAllowedTypes('show_at_main_page', ['null', 'boolean'])
        ;
    }
}
