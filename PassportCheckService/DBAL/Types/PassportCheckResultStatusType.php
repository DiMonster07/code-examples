<?php

namespace App\PassportCheckService\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

/**
 * Class PassportCheckResultStatusType.
 */
class PassportCheckResultStatusType extends AbstractEnumType
{
    public const VALID                 = 'valid';
    public const INVALID               = 'invalid';
    public const INVALID_REQUEST_DATA  = 'invalid_request_data';
    public const INVALID_RESPONSE_DATA = 'invalid_response_data';
    public const CAPTURE_REQUIRED      = 'capture_required';
    public const UNKNOWN_ERROR         = 'unknown_error';

    /**
     * @var array
     */
    protected static $choices = [
        self::VALID                 => 'passport_check_result_status.valid',
        self::INVALID               => 'passport_check_result_status.invalid',
        self::INVALID_REQUEST_DATA  => 'passport_check_result_status.invalid_request_data',
        self::INVALID_RESPONSE_DATA => 'passport_check_result_status.invalid_response_data',
        self::CAPTURE_REQUIRED      => 'passport_check_result_status.capture_required',
        self::UNKNOWN_ERROR         => 'passport_check_result_status.unknown_error',
    ];
}
