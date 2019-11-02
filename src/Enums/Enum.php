<?php

declare(strict_types=1);

namespace Aegisub\Enums;

use ReflectionClass;
use ReflectionException;

class Enum
{
    /**
     * Give all values of the class.
     *
     * @return array
     * @throws ReflectionException
     */
    public static function getValues(): array
    {
        $reflectionClass = new ReflectionClass(__CLASS__);
        return array_values($reflectionClass->getConstants());
    }
}