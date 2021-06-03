<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * AbstractEntityRepository.
 */
abstract class AbstractEntityRepository extends ServiceEntityRepository implements RepositoryInterface
{
    use EntityRepositoryTrait {
        EntityRepositoryTrait::traitAdd as public add;
        EntityRepositoryTrait::traitGetAlias as public getAlias;
        EntityRepositoryTrait::traitGetSimpleQB as public getSimpleQB;
        EntityRepositoryTrait::traitRemove as public remove;
        EntityRepositoryTrait::traitGetSimpleQBWithAlias as public getSimpleQBWithAlias;
    }

    /**
     * @param array $criteria
     *
     * @throws NonUniqueResultException
     *
     * @return object|null
     */
    public function getOne(array $criteria = []): ?object
    {
        return $this->getAllQB($criteria)->getQuery()->getOneOrNullResult();
    }

    /**
     * @param array $criteria
     *
     * @return object[]
     */
    public function getAll(array $criteria = []): array
    {
        return $this->getAllQB($criteria)->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(array $criteria = []): int
    {
        return $this->getCountQB($criteria)->getQuery()->execute(null, AbstractQuery::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * {@inheritdoc}
     */
    public function getCountQB(array $criteria = []): QueryBuilder
    {
        return $this->getAllQB($criteria)->select("COUNT({$this->getAlias()})");
    }

    /**
     * {@inheritdoc}
     */
    public function getFields(string $fieldName, bool $distinct = false): array
    {
        $alias = $this->getAlias();
        $qb    = $this->getSimpleQB()
            ->select("{$alias}.{$fieldName} AS field")
            ->distinct($distinct)
        ;

        return array_map(
            /** @phpstan-ignore-next-line */
            function ($elem) {
                return $elem['field'];
            },
            $qb->getQuery()->getScalarResult()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifiers(array $criteria): array
    {
        $result = $this
            ->getAllQB($criteria)
            ->select(sprintf('%s.id', $this->getAlias()))
            ->getQuery()
            ->getScalarResult()
        ;

        return array_column($result, 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function getAllQB(array $criteria = []): QueryBuilder
    {
        $resolver = new OptionsResolver();
        $this->configureCriteria($resolver);
        $options = $resolver->resolve($criteria);

        $qb = $this->getSimpleQB();
        $this->applyCriteria($qb, $options);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference(int $id)
    {
        return $this->getEntityManager()->getReference($this->getClassName(), $id);
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureCriteria(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('id', null)
            ->setAllowedTypes('id', ['null', 'int', 'string', 'array'])
        ;

        /* @phpstan-ignore-next-line */
        $resolver->setNormalizer('id', function (Options $options, $value) { //@phan-suppress-current-line PhanUnusedClosureParameter
            if (is_numeric($value)) {
                return (int) $value;
            }

            return $value;
        });
    }

    /**
     * @param QueryBuilder $qb
     * @param array        $criteria
     */
    protected function applyCriteria(QueryBuilder $qb, array $criteria): void
    {
        $alias = $this->getAlias();
        if ($criteria['id'] !== null) {
            $qb
                ->andWhere(is_array($criteria['id']) ? "{$alias}.id IN (:id)" : "{$alias}.id = :id")
                ->setParameter('id', $criteria['id'])
            ;
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $field
     * @param mixed        $value
     * @param string|null  $alias
     * @param string       $operator
     *
     * @return $this
     */
    protected function applySimpleCriteria(QueryBuilder $qb, string $field, $value, ?string $alias = null, string $operator = self::OPERATOR_EQ): self
    {
        if ($value === null) {
            return $this;
        }
        $inflector = InflectorFactory::create()->build();

        $alias     = $alias ?? $this->getAlias();
        $paramName = $inflector->tableize(sprintf('%s_%s', $alias, $field));

        $paramName = static::OPERATOR_IN === $operator ? "(:{$paramName})" : ":{$paramName}";

        $qb
            ->andWhere(sprintf('%s.%s %s %s', $alias, $field, $operator, $paramName))
            ->setParameter($paramName, $value)
        ;

        return $this;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $field
     * @param array|null   $values
     * @param string|null  $alias
     *
     * @return $this
     */
    protected function applyArrayCriteria(QueryBuilder $qb, string $field, ?array $values, ?string $alias = null): self
    {
        if ($values === null || count($values) === 0) {
            return $this;
        }

        $inflector = InflectorFactory::create()->build();

        $alias = $alias ?? $this->getAlias();
        $param = $inflector->tableize(sprintf('%s_%s', $alias, $field));
        $expr  = new Expr\Orx();
        foreach ($values as $k => $value) {
            $paramName = str_replace('.', '_', sprintf('%s_%d', $param, $k));
            $expr->add(new Expr\Orx(sprintf('%s.%s = :%s', $alias, $field, $paramName)));
            $qb->setParameter($paramName, $value);
        }

        $qb->andWhere($expr);

        return $this;
    }

    /**
     * @phan-suppress PhanMismatchVariadicParam
     *
     * @param OptionsResolver $resolver
     * @param string[]        $fields
     *
     * @return $this
     */
    protected function setBooleanCriteria(OptionsResolver $resolver, array $fields): self
    {
        foreach ($fields as $field) {
            $resolver
                ->setDefault($field, null)
                ->setAllowedTypes($field, ['null', 'boolean'])
            ;
        }

        return $this;
    }

    /**
     * @param OptionsResolver $resolver
     * @param string          $option
     * @param string|null     $instanceOf
     * @param string|null     $type
     * @param callable|null   $callable
     *
     * @psalm-suppress InvalidArgument
     *
     * @return $this
     */
    protected function setArrayCriteria(OptionsResolver $resolver, string $option, ?string $instanceOf, ?string $type = null, ?callable $callable = null): self
    {
        $resolver->setDefault($option, null);

        $resolver->setAllowedTypes($option, ['null', 'array', $instanceOf ?? $type]);

        /* @phpstan-ignore-next-line */
        $resolver->setNormalizer($option, function (Options $options, $value): ?array { // @phan-suppress-current-line PhanUnusedClosureParameter
            return $value !== null ? (array) $value : $value;
        });

        if ($callable === null) {
            /** @phpstan-ignore-next-line */
            $callable = function ($value) use ($instanceOf): bool {
                if ($instanceOf !== null) {
                    return (bool) $value && $value instanceof $instanceOf;
                }

                return (bool) $value;
            };
        }

        /* @phpstan-ignore-next-line */
        $resolver->setAllowedValues($option, function ($values) use ($callable): bool {
            if ($values === null) {
                return true;
            }

            $result = true;
            foreach ((array) $values as $value) {
                $result = $result && $callable($value);
            }

            return $result;
        });

        return $this;
    }
}
