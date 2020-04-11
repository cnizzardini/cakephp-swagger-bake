<?php


namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

class RequestBody implements JsonSerializable
{
    private $description = '';
    private $content = [];
    private $required = false;

    public function toArray() : array
    {
        $vars = get_object_vars($this);
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
}