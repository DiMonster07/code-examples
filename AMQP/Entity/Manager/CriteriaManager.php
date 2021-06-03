<?php

namespace App\Entity\Manager;

use App\Entity\Repository\EntityRepository;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CriteriaManager.
 *
 * @method EntityRepository getRepository()
 */
class CriteriaManager extends AbstractManager implements CriteriaManagerInterface
{
    public const OPERATOR_EQUAL     = '=';
    public const OPERATOR_NOT_EQUAL = '!=';
    public const OPERATOR_GT        = '>';
    public const OPERATOR_GE        = '>=';
    public const OPERATOR_LT        = '<';
    public const OPERATOR_LE        = '<=';
    public const OPERATOR_IN        = 'IN';

    /**
     * {@inheritdoc}
     */
    public function getOne(array $criteria = [])
    {
        return $this
            ->getAllQB($criteria)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param array $criteria
     *
     * @return int[]|array
     */
    public function getIdentifiers(array $criteria)
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
    public function getAll(array $criteria = [])
    {
        return $this
            ->getAllQB($criteria)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(array $criteria = [])
    {
        $alias = $this->getAlias();

        return $this
            ->getAllQB($criteria)
            ->select("COUNT({$alias})")
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllQB(array $criteria = [])
    {
        $resolver = new OptionsResolver();
        $this->configureCriteria($resolver);
        $options = $resolver->resolve($criteria);

        $qb = $this->getRepository()->getSimpleQB();

        $this->applyCriteria($qb, $options);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function applyCriteria(QueryBuilder $qb, array $criteria)
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
     * @param int $id
     *
     * @throws ORMException
     *
     * @return bool|\Doctrine\Common\Proxy\Proxy|object|null
     */
    public function getReference($id)
    {
        return $this->getEntityManager()->getReference($this->class, $id);
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $field
     * @param mixed        $value
     * @param string|null  $alias
     * @param string       $operator
     *
     * @return CriteriaManager
     */
    protected function applySimpleCriteria(QueryBuilder $qb, $field, $value, $alias = null, $operator = self::OPERATOR_EQUAL)
    {
        if ($value === null) {
            return $this;
        }

        $alias     = $alias ?: $this->getAlias();
        $paramName = Inflector::tableize(sprintf('%s_%s', $alias, $field));

        $param = static::OPERATOR_IN === $operator ? "(:{$paramName})" : ":{$paramName}";

        $qb
            ->andWhere(sprintf('%s.%s %s %s', $alias, $field, $operator, $param))
            ->setParameter($paramName, $value)
        ;

        return $this;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $field
     * @param array|mixed  $values
     * @param string|null  $alias
     *
     * @return CriteriaManager
     */
    protected function applyArrayCriteria(QueryBuilder $qb, $field, $values, $alias = null)
    {
        if ($values === null) {
            return $this;
        }

        $alias = $alias ?: $this->getAlias();
        $param = Inflector::tableize(sprintf('%s_%s', $alias, $field));

        $expr = new Expr\Orx();
        foreach ((array) $values as $k => $value) {
            $paramName = preg_replace('/\./', '_', sprintf('%s_%d', $param, $k));
            $expr->add(new Expr\Orx(sprintf('%s.%s = :%s', $alias, $field, $paramName)));
            $qb->setParameter($paramName, $value);
        }

        $qb->andWhere($expr);

        return $this;
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureCriteria(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'id' => null,
        ]);

        $resolver->setAllowedTypes('id', ['null', 'int', 'string', 'array']);

        $className = $this->getClass();

        $resolver->setNormalizer('id', function (Options $options, $value) use ($className) { // @phan-suppress-current-line PhanUnusedClosureParameter
            if (is_numeric($value)) {
                return (int) $value;
            }

            if (!is_array($value)) {
                return $value;
            }

            $ids = [];
            foreach ($value as $v) {
                $ids[] = $v instanceof $className ? $v->getId() : $v;
            }

            return $ids;
        });
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @return CriteriaManager
     */
    protected function setBooleanCriteria(OptionsResolver $resolver)
    {
        foreach (func_get_args() as $field) {
            if ($field instanceof OptionsResolver) {
                continue;
            }

            $resolver->setDefault($field, null);
            $resolver->setAllowedTypes($field, ['null', 'boolean']);
        }

        return $this;
    }

    /**
     * @param OptionsResolver $resolver
     * @param string          $option
     * @param string|null     $instanceOf
     * @param string          $type
     * @param callable|null   $callable
     *
     * @return CriteriaManager
     */
    protected function setArrayCriteria(OptionsResolver $resolver, $option, $instanceOf, $type, $callable = null)
    {
        $resolver->setDefault($option, null);

        $resolver->setAllowedTypes($option, ['null', 'array', $instanceOf ?: $type]);

        $resolver->setNormalizer($option, function (Options $options, $value) { // @phan-suppress-current-line PhanUnusedClosureParameter
            return $value !== null ? (array) $value : $value;
        });

        if (!$callable) {
            $callable = function ($value) use ($instanceOf) {
                if ($instanceOf) {
                    return (bool) $value && $value instanceof $instanceOf;
                }

                return (bool) $value;
            };
        }

        $resolver->setAllowedValues($option, function ($values) use ($callable) {
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

    /**
     * @return string
     */
    protected function getAlias()
    {
        return $this->getRepository()->getAlias();
    }
}
