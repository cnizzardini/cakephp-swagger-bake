<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Utility;

/**
 * Class OpenApiDataType
 *
 * @package SwaggerBake\Lib\Utility
 */
class OpenApiDataType
{
    public const BOOLEAN = 'boolean';
    public const ARRAY = 'array';
    public const INTEGER = 'integer';
    public const NUMBER = 'number';
    public const OBJECT = 'object';
    public const STRING = 'string';
    public const JSON = 'json';
    public const EMPTY = '';

    /**
     * @var string[]
     */
    public const TYPES = [
        self::ARRAY,
        self::BOOLEAN,
        self::INTEGER,
        self::NUMBER,
        self::OBJECT,
        self::STRING,
        self::JSON,
        self::EMPTY,
    ];
}
