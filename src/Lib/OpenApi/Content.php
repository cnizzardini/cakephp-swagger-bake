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
    /**
     * @var string
     */
    private $mimeType;

    /**
     * @var string|\SwaggerBake\Lib\OpenApi\Schema
     */
    private $schema;

    /**
     * @return array
     */
    public function toArray(): array
    {
        $vars = get_object_vars($this);
        unset($vars['mimeType']);
        unset($vars['schema']);

        switch (gettype($this->schema)) {
            case 'string':
                $vars['schema']['$ref'] = $this->schema;
                break;
            case 'object':
                $vars['schema'] = $this->schema;
                break;
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
}
