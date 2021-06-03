<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

/**
 * Class InnPassportCheckServiceStatusType.
 */
class InnPassportCheckServiceStatusType extends AbstractEnumType
{
    public const NOT_PROCESSED           = 'not_processed';
    public const IN_INTERNAL_PROCESS     = 'in_internal_process';
    public const IN_FRONT_FORM_PROCESS   = 'in_front_form_process';
    public const VALID_INTERNAL          = 'valid_internal';
    public const VALID_IN_FRONT_FORM     = 'valid_in_front_form';
    public const NOT_VALID_INTERNAL      = 'not_valid_internal';
    public const NOT_VALID_IN_FRONT_FORM = 'not_valid_in_front_form';
    public const FAILED                  = 'failed';

    /**
     * @var array
     */
    protected static $choices = [
        self::NOT_PROCESSED           => 'inn_passport_check_service_status_type.not_processed',
        self::IN_INTERNAL_PROCESS     => 'inn_passport_check_service_status_type.in_internal_process',
        self::IN_FRONT_FORM_PROCESS   => 'inn_passport_check_service_status_type.in_front_form_process',
        self::VALID_INTERNAL          => 'inn_passport_check_service_status_type.valid_internal',
        self::VALID_IN_FRONT_FORM     => 'inn_passport_check_service_status_type.valid_in_front_form',
        self::NOT_VALID_INTERNAL      => 'inn_passport_check_service_status_type.not_valid_internal',
        self::NOT_VALID_IN_FRONT_FORM => 'inn_passport_check_service_status_type.not_valid_in_front_form',
        self::FAILED                  => 'inn_passport_check_service_status_type.failed',
    ];
}
