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
     * Returns the OpenApi data type from the CakePHP data type
     *
     * @param string $type Data type
     * @return string
     */
    public static function toType(string $type): string
    {
        switch ($type) {
            case 'int':
            case 'integer':
            case 'tinyinteger':
            case 'smallinteger':
            case 'biginteger':
            case 'mediuminteger':
                return 'integer';
            case 'decimal':
            case 'float':
                return 'number';
            case 'uuid':
            case 'text':
            case 'varchar':
            case 'char':
            case 'date':
            case 'time':
            case 'datetime':
                return 'string';
            case 'boolean':
            case 'bool':
                return 'boolean';
        }

        return $type;
    }

    /**
     * Returns a data format from CakePHP data type
     *
     * @param string $type Data type
     * @return string
     */
    public static function toFormat(string $type): string
    {
        switch ($type) {
            case 'int':
            case 'integer':
            case 'biginteger':
                return 'int64';
            case 'smallinteger':
            case 'tinyinteger':
            case 'mediuminteger':
                return 'int32';
            case 'decimal':
            case 'float':
                return 'float';
            case 'uuid':
                return 'uuid';
            case 'text':
            case 'varchar':
            case 'char':
                return 'string';
            case 'date':
                return 'date';
            case 'time':
                return 'time';
            case 'datetime':
                return 'date-time';
        }

        return '';
    }
}
