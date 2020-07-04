<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

/**
 * Class Content
 *
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
        'application/x-www-form-urlencoded',
    ];

    /**
     * @var string
     */
    private $mimeType = '';

    /**
     * @var string|\SwaggerBake\Lib\OpenApi\Schema
     */
    private $schema;

    /**
     * @var string $type value can be string, number etc.
     */
    private $type = '';

    /**
     * @var string $format value can be binary for images for instance
     */
    private $format = '';

    /**
     * @return array
     */
    public function toArray(): array
    {
        $vars = get_object_vars($this);
        unset($vars['mimeType']);
        if (is_null($this->schema)) {
            unset($vars['schema']);
            $vars['schema'] = '';
        } elseif (is_string($this->schema)) {
            unset($vars['schema']);
            $vars['schema']['$ref'] = $this->schema;
        }

        if (in_array($this->mimeType, self::STANDARD_FORMATS)) {
            unset($vars['type']);
            unset($vars['format']);
        }

        return $vars;
    }

    /**
     * @return array|mixed
     */
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
     * @param string $mimeType Mime type e.g. application/json, application/xml, etc...
     * @return $this
     */
    public function setMimeType(string $mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Can be either a schema $ref string such as '#/components/schemas/Pet' or a Schema instance.
     *
     * @param string|\SwaggerBake\Lib\OpenApi\Schema $schema Schema
     * @return $this
     */
    public function setSchema($schema)
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
     * @param string $type value can be string, number etc.
     * @return $this
     */
    public function setType(string $type)
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
     * @param string $format value can be binary for images for instance
     * @return $this
     */
    public function setFormat(string $format)
    {
        $this->format = $format;

        return $this;
    }
}
