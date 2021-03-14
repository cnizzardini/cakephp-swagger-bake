<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Utility;

/**
 * Class DataTypeConversion
 *
 * @package SwaggerBake\Lib\Utility
 */
class DataTypeConversion
{
    /**
     * Returns an OpenApi data type from the CakePHP data type, will default to returning argument if no match found
     *
     * @param string $cakeType Data type
     * @return string
     */
    public static function toType(string $cakeType): string
    {
        $typeMap = [
            // openapi type => ['cake','types']
            'integer' => ['int','integer','tinyinteger','smallinteger','biginteger','mediuminteger'],
            'number' => ['decimal','float'],
            'string' => ['uuid','text','varchar','char','date','time','datetime'],
            'boolean' => ['bool','boolean'],
        ];

        foreach ($typeMap as $type => $types) {
            if (in_array($cakeType, $types)) {
                return $type;
            }
        }

        return $cakeType;
    }

    /**
     * Returns an OpenAPI data format from CakePHP data type
     *
     * @param string $cakeType Data type
     * @return string
     */
    public static function toFormat(string $cakeType): string
    {
        $typeMap = [
            // openapi format => ['cake','types']
            'int64' => ['int','integer','biginteger'],
            'int32' => ['tinyinteger','smallinteger','mediuminteger'],
            'float' => ['decimal','float'],
            'uuid' => ['uuid'],
            'string' => ['text','varchar','char'],
            'date' => ['date'],
            'time' => ['time'],
            'date-time' => ['datetime'],
        ];

        foreach ($typeMap as $type => $types) {
            if (in_array($cakeType, $types)) {
                return $type;
            }
        }

        return '';
    }
}
