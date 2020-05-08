<?php


namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

class RequestBody implements JsonSerializable
{
    /** @var string  */
    private $description = '';

    /** @var Content[]  */
    private $content = [];

    /** @var bool  */
    private $required = false;

    /** @var bool  */
    private $ignoreCakeSchema = false;

    /**
     * @return array
     */
    public function toArray() : array
    {
        $vars = get_object_vars($this);
        unset($vars['ignoreCakeSchema']);
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

    /**
     * @return array|Content[]
     */
    public function getContent() : array
    {
        return $this->content;
    }

    /**
     * @param Content $content
     * @return $this
     */
    public function pushContent(Content $content) : RequestBody
    {
        $this->content[$content->getMimeType()] = $content;
        return $this;
    }

    /**
     * @param string $mimeType
     * @return Content|null
     */
    public function getContentByType(string $mimeType) : ?Content
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