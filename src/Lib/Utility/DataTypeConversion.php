<?php


namespace SwaggerBake\Lib\Utility;


class DataTypeConversion
{
    public static function convert(string $type)
    {
        switch ($type)
        {
            case 'int':
            case 'smallinteger':
            case 'biginteger':
            case 'mediuminteger':
                return 'integer';
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