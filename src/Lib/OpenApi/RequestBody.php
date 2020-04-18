<?php


namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

class RequestBody implements JsonSerializable
{
    private $description = '';
    private $content = [];
    private $required = false;
    private $ignoreCakeSchema = false;

    public function toArray() : array
    {
        $vars = get_object_vars($this);
        unset($vars['ignoreCakeSchema']);
        return $vars;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return RequestBody
     */
    public function setDescription(string $description): RequestBody
    {
        $this->description = $description;
        return $this;
    }

    public function getContent() : array
    {
        return $this->content;
    }

    public function pushContent(Content $content) : RequestBody
    {
        $this->content[$content->getMimeType()] = $content;
        return $this;
    }

    public function getContentByType(string $mimeType) : ?array
    {
        if (isset($this->content[$mimeType])) {
            return $this->content[$mimeType];
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     * @return RequestBody
     */
    public function setRequired(bool $required): RequestBody
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIgnoreCakeSchema(): bool
    {
        return $this->ignoreCakeSchema;
    }

    /**
     * @param bool $ignoreCakeSchema
     * @return RequestBody
     */
    public function setIgnoreCakeSchema(bool $ignoreCakeSchema): RequestBody
    {
        $this->ignoreCakeSchema = $ignoreCakeSchema;
        return $this;
    }
}