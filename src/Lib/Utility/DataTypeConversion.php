<?php

namespace SwaggerBake\Lib\Utility;

/**
 * Class DataTypeConversion
 * @package SwaggerBake\Lib\Utility
 */
class DataTypeConversion
{
    public static function toType(string $type) : string
    {
        switch ($type)
        {
            case 'int':
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
        }

        return $type;
    }

    public static function toFormat(string $type) : string
    {
        switch ($type)
        {
            case 'int':
            case 'biginteger':
                return 'int32';
            case 'smallinteger':
            case 'mediuminteger':
                return 'int64';
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

        return $type;
    }
}