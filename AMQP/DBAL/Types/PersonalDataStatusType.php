<?php

namespace App\DBAL\Types;

/**
 * Class PersonalDataStatusType.
 */
class PersonalDataStatusType extends ColorableEnumType
{
    public const NOT_PROCESSED                  = 'not_processed';
    public const SENT_REQUEST_TO_CLIENT         = 'sent_request_to_client';
    public const AUTO_PROCESS_COMPLETED         = 'auto_process_completed';
    public const AUTO_PROCESS_FAILED            = 'auto_process_failed';
    public const MANUALLY_CHECK_COMPLETED       = 'manually_check_completed';
    public const MANUALLY_CHECK_FAILED          = 'manually_check_failed';
    public const NOT_CONFIRMED                  = 'not_confirmed';
    public const CHECKED_EARLIER                = 'checked_earlier';
    public const MANUALLY_FORCE_CHECK_COMPLETED = 'manually_force_check_completed';

    /**
     * {@inheritdoc}
     */
    protected static $choices = [
        self::NOT_PROCESSED                  => 'personal_data_status.not_processed',
        self::SENT_REQUEST_TO_CLIENT         => 'personal_data_status.sent_request_to_client',
        self::AUTO_PROCESS_COMPLETED         => 'personal_data_status.auto_process_completed',
        self::AUTO_PROCESS_FAILED            => 'personal_data_status.auto_process_failed',
        self::MANUALLY_CHECK_COMPLETED       => 'personal_data_status.manually_check_completed',
        self::MANUALLY_CHECK_FAILED          => 'personal_data_status.manually_check_failed',
        self::NOT_CONFIRMED                  => 'personal_data_status.not_confirmed',
        self::CHECKED_EARLIER                => 'personal_data_status.checked_earlier',
        self::MANUALLY_FORCE_CHECK_COMPLETED => 'personal_data_status.manually_force_check_completed',
    ];

    /**
     * {@inheritdoc}
     */
    protected static $colorMap = [
        self::NOT_PROCESSED                  => self::UNAVAILABLE_STYLE,
        self::SENT_REQUEST_TO_CLIENT         => self::PROCESS_STYLE,
        self::AUTO_PROCESS_COMPLETED         => self::SUCCESS_STYLE,
        self::AUTO_PROCESS_FAILED            => self::ERROR_STYLE,
        self::MANUALLY_CHECK_COMPLETED       => self::SUCCESS_STYLE,
        self::MANUALLY_CHECK_FAILED          => self::ERROR_STYLE,
        self::NOT_CONFIRMED                  => self::ERROR_STYLE,
        self::CHECKED_EARLIER                => self::SUCCESS_STYLE,
        self::MANUALLY_FORCE_CHECK_COMPLETED => self::SUCCESS_STYLE,
    ];
}
