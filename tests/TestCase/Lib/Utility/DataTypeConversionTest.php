<?php

namespace SwaggerBake\Test\TestCase\Lib\Utility;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Utility\DataTypeConversion;

class DataTypeConversionTest extends TestCase
{
    // OpenAPI Types:
    private const OA_TYPE_INTEGER = 'integer';
    private const OA_TYPE_NUMBER = 'number';
    private const OA_TYPE_STRING = 'string';
    private const OA_TYPE_BOOLEAN = 'boolean';

    // OpenAPI Formats:
    private const OA_FORMAT_INT64 = 'int64';
    private const OA_FORMAT_INT32 = 'int32';
    private const OA_FORMAT_FLOAT = 'float';
    private const OA_FORMAT_DATETIME = 'date-time';

    public function testToType(): void
    {
        $types = [
            'int' => self::OA_TYPE_INTEGER,
            'integer' => self::OA_TYPE_INTEGER,
            'tinyinteger' => self::OA_TYPE_INTEGER,
            'smallinteger' => self::OA_TYPE_INTEGER,
            'biginteger' => self::OA_TYPE_INTEGER,
            'mediuminteger' => self::OA_TYPE_INTEGER,
            'decimal' => self::OA_TYPE_NUMBER,
            'float' => self::OA_TYPE_NUMBER,
            'uuid' => self::OA_TYPE_STRING,
            'text' => self::OA_TYPE_STRING,
            'varchar' => self::OA_TYPE_STRING,
            'char' => self::OA_TYPE_STRING,
            'date' => self::OA_TYPE_STRING,
            'time' => self::OA_TYPE_STRING,
            'datetime' => self::OA_TYPE_STRING,
            'boolean' => self::OA_TYPE_BOOLEAN,
            'bool' => self::OA_TYPE_BOOLEAN,
            'timestamp' => self::OA_TYPE_STRING,
            'timestampfractional' => self::OA_TYPE_STRING,
        ];

        foreach ($types as $dbType => $openApiType) {
            $this->assertEquals(
                $openApiType,
                DataTypeConversion::toType($dbType),
                "DB Type `$dbType` failed to match to OpenAPI type `$openApiType`"
            );
        }
    }

    public function testToFormat(): void
    {
        $types = [
            'int' => self::OA_FORMAT_INT64,
            'integer' => self::OA_FORMAT_INT64,
            'biginteger' => self::OA_FORMAT_INT64,
            'tinyinteger' => self::OA_FORMAT_INT32,
            'smallinteger' => self::OA_FORMAT_INT32,
            'mediuminteger' => self::OA_FORMAT_INT32,
            'decimal' => self::OA_FORMAT_FLOAT,
            'float' => self::OA_FORMAT_FLOAT,
            'uuid' => 'uuid',
            'text' => self::OA_TYPE_STRING,
            'varchar' => self::OA_TYPE_STRING,
            'char' => self::OA_TYPE_STRING,
            'date' => 'date',
            'time' => 'time',
            'datetime' => self::OA_FORMAT_DATETIME,
            'timestamp' => self::OA_FORMAT_DATETIME,
            'timestampfractional' => self::OA_FORMAT_DATETIME,
        ];

        foreach ($types as $dbType => $openApiFormat) {
            $this->assertEquals(
                $openApiFormat,
                DataTypeConversion::toFormat($dbType),
                "DB Type `$dbType` failed to match to OpenAPI format `$openApiFormat`"
            );
        }
    }
}