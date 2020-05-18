<?php

namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

/**
 * Class Content
 * @package SwaggerBake\Lib\OpenApi
 * @see https://swagger.io/docs/specification/describing-request-body/
 */
class Content implements JsonSerializable
{
    /** @var string[]  */
    private const STANDARD_FORMATS = [
        'application/json',
        'application/xml',
        'application/vnd.api+json',
        'application/x-www-form-urlencoded'
    ];

    /** @var string  */
    private $mimeType = '';

    /** @var string|Schema */
    private $schema;

    /** @var string $type value can be string, number etc. */
    private $type = '';

    /** @var string $format value can be binary for images for instance */
    private $format = '';

    public function toArray() : array
    {
        $vars = get_object_vars($this);
        unset($vars['mimeType']);
        if (is_string($this->schema)) {
            unset($vars['schema']);
            $vars['schema']['$ref'] = $this->schema;
        }

        if (in_array($this->mimeType, self::STANDARD_FORMATS)) {
            unset($vars['type']);
            unset($vars['format']);
        }

        return $vars;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     * @return Content
     */
    public function setMimeType(string $mimeType): Content
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSchema() : Schema
    {
        return $this->schema;
    }

    /**
     * Can be either a schema $ref string such as '#/components/schemas/Pet' or a Schema instance.
     *
     * @param string|Schema $schema
     * @return Content
     */
    public function setSchema($schema) : Content
    {
        $this->schema = $schema;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Content
     */
    public function setType(string $type): Content
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param string $format
     * @return Content
     */
    public function setFormat(string $format): Content
    {
        $this->format = $format;
        return $this;
    }
}