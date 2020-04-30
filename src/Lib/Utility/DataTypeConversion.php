<?php


namespace SwaggerBake\Lib\Utility;


class DataTypeConversion
{
    public static function convert(string $type) : string
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
}