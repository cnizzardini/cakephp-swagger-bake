<?php


namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

class Content implements JsonSerializable
{
    private $mimeType = '';
    private $schema;

    public function toArray() : array
    {
        $vars = get_object_vars($this);
        unset($vars['mimeType']);
        if (is_string($this->schema)) {
            unset($vars['schema']);
            $vars['schema']['$ref'] = $this->schema;
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
}