<?php

namespace App\Entity\Manager;

use App\DBAL\Types\InnPassportCheckServiceStatusType;
use App\DBAL\Types\PassportVerifyStatusType;
use App\Entity\PersonalData;
use App\Entity\Repository\PersonalDataRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PersonalDataManager.
 *
 * @method PersonalData|null      getOne(array $criteria = [])
 * @method PersonalData[]         getAll(array $criteria = [])
 * @method PersonalDataRepository getRepository()
 */
class PersonalDataManager extends CriteriaManager
{
    /**
     * @param int|null $maxResults
     *
     * @return PersonalData[]|array
     */
    public function getAllInProcess(int $maxResults = null)
    {
        $qb = $this
            ->getAllQB([
                'passport_verify_status' => [
                    PassportVerifyStatusType::IN_PROCESS,
                    PassportVerifyStatusType::IN_FRONT_FORM_PROCESS,
                    PassportVerifyStatusType::IN_INTERNAL_PROCESS,
                ],
                'blocked' => false,
            ])
        ;

        if ($maxResults !== null) {
            $qb->setMaxResults($maxResults);
        }

        return $qb
            ->orderBy('rand()')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param int|null $maxResults
     *
     * @return PersonalData[]|array
     */
    public function getAllWaitStartProcess(int $maxResults = null)
    {
        $qb = $this
            ->getAllQB([
                'passport_verify_status' => [
                    PassportVerifyStatusType::WAIT_START_PROCESS,
                    PassportVerifyStatusType::WAIT_START_INTERNAL_PROCESS,
                ],
                'blocked' => false,
            ])
        ;

        if ($maxResults !== null) {
            $qb->setMaxResults($maxResults);
        }

        return $qb
            ->orderBy('rand()')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param array $ids
     * @param bool  $blockedState
     */
    public function changeBlockedState(array $ids, bool $blockedState)
    {
        $this
            ->getRepository()
            ->getSimpleQB()
            ->update()
            ->set("{$this->getAlias()}.blocked", ':blockedState')
            ->andWhere("{$this->getAlias()}.id IN (:ids)")
            ->setParameter('blockedState', (int) $blockedState)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function applyCriteria(QueryBuilder $qb, array $criteria)
    {
        parent::applyCriteria($qb, $criteria);

        $this->applySimpleCriteria($qb, 'passportSeries', $criteria['passport_series']);
        $this->applySimpleCriteria($qb, 'passportNumber', $criteria['passport_number']);

        if ($criteria['passport_verify_status']) {
            if (is_array($criteria['passport_verify_status'])) {
                $this->applyArrayCriteria($qb, 'passportVerifyStatus', $criteria['passport_verify_status']);
            } else {
                $this->applySimpleCriteria($qb, 'passportVerifyStatus', $criteria['passport_verify_status']);
            }
        }

        if ($criteria['inn_passport_check_service_status']) {
            if (is_array($criteria['inn_passport_check_service_status'])) {
                $this->applyArrayCriteria($qb, 'innPassportCheckServiceStatus', $criteria['inn_passport_check_service_status']);
            } else {
                $this->applySimpleCriteria($qb, 'innPassportCheckServiceStatus', $criteria['inn_passport_check_service_status']);
            }
        }

        if ($criteria['china_personal_data']) {
            $qb
                ->andWhere("{$this->getAlias()}.supplyingType in ('china_api', 'china_manual')")
            ;
        }

        if ($criteria['russia_personal_data']) {
            $qb
                ->andWhere("{$this->getAlias()}.supplyingType in ('russia_copy', 'russia_form')")
            ;
        }

        if ($criteria['blocked'] !== null) {
            $blockedState = (int) $criteria['blocked'];

            $qb
                ->andWhere("{$this->getAlias()}.blocked={$blockedState}")
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureCriteria(OptionsResolver $resolver)
    {
        parent::configureCriteria($resolver);

        $resolver->setDefaults([
            'passport_series'                   => null,
            'passport_number'                   => null,
            'passport_verify_status'            => null,
            'inn_passport_check_service_status' => null,
        ]);

        $this->setBooleanCriteria($resolver,
            'china_personal_data',
            'russia_personal_data',
            'blocked'
        );

        $this
            ->setArrayCriteria($resolver, 'passport_verify_status', null, 'string', static function ($value) {
                return is_string($value) && PassportVerifyStatusType::isValueExist($value);
            })
        ;

        $this
            ->setArrayCriteria($resolver, 'inn_passport_check_service_status', null, 'string', static function ($value) {
                return is_string($value) && InnPassportCheckServiceStatusType::isValueExist($value);
            })
        ;

        $resolver->setAllowedTypes('passport_series', ['null', 'int', 'string']);
        $resolver->setAllowedTypes('passport_number', ['null', 'int', 'string']);
    }
}
