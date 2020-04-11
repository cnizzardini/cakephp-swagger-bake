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
        return array_filter($vars, function ($var) {
            return !empty($var) ? true : null;
        });
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
     * @param mixed $schema
     * @return Content
     */
    public function setSchema(Schema $schema) : Content
    {
        $this->schema = $schema;
        return $this;
    }
}