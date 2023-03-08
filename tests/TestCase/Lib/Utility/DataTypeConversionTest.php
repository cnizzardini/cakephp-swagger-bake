<?php

namespace SwaggerBake\Test\TestCase\Lib\Utility;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Utility\DataTypeConversion;
use SwaggerBake\Lib\Utility\OpenApiDataType;

class DataTypeConversionTest extends TestCase
{
    // OpenAPI Formats:
    private const OA_FORMAT_INT64 = 'int64';
    private const OA_FORMAT_INT32 = 'int32';
    private const OA_FORMAT_FLOAT = 'float';

    public function testToType(): void
    {
        $types = [
            'int' => OpenApiDataType::INTEGER,
            'integer' => OpenApiDataType::INTEGER,
            'tinyinteger' => OpenApiDataType::INTEGER,
            'smallinteger' => OpenApiDataType::INTEGER,
            'biginteger' => OpenApiDataType::INTEGER,
            'mediuminteger' => OpenApiDataType::INTEGER,
            'decimal' => OpenApiDataType::NUMBER,
            'float' => OpenApiDataType::NUMBER,
            'uuid' => OpenApiDataType::STRING,
            'text' => OpenApiDataType::STRING,
            'varchar' => OpenApiDataType::STRING,
            'char' => OpenApiDataType::STRING,
            'date' => OpenApiDataType::STRING,
            'time' => OpenApiDataType::STRING,
            'datetime' => OpenApiDataType::STRING,
            'boolean' => OpenApiDataType::BOOLEAN,
            'bool' => OpenApiDataType::BOOLEAN,
            'timestamp' => OpenApiDataType::STRING,
            'timestampfractional' => OpenApiDataType::STRING,
            'json' => OpenApiDataType::JSON
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
            'text' => OpenApiDataType::STRING,
            'varchar' => OpenApiDataType::STRING,
            'char' => OpenApiDataType::STRING,
            'date' => 'date',
            'time' => 'time',
            'datetime' => 'date-time',
            'timestamp' => 'date-time',
            'timestampfractional' => 'date-time',
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