<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

/**
 * Class ColorableEnumType.
 */
class ColorableEnumType extends AbstractEnumType
{
    public const SUCCESS_STYLE     = 'bg-green';
    public const PROCESS_STYLE     = 'bg-aqua';
    public const ERROR_STYLE       = 'bg-red';
    public const WARNING_STYLE     = 'bg-yellow';
    public const NEUTRAL_STYLE     = 'bg-gray';
    public const UNAVAILABLE_STYLE = 'bg-black';

    /**
     * @var array
     *
     * @suppress PhanReadOnlyProtectedProperty
     */
    protected static $colorMap = [];

    /**
     * @return array
     */
    public static function getColorMap()
    {
        return static::$colorMap;
    }
}
