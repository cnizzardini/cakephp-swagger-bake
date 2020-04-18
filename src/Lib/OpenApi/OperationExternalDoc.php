<?php

namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

class OperationExternalDoc implements JsonSerializable
{
    private $description = '';
    private $url = '';

    public function toArray() : array
    {
        return get_object_vars($this);
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
     * @return OperationExternalDoc
     */
    public function setDescription(string $description): OperationExternalDoc
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return OperationExternalDoc
     */
    public function setUrl(string $url): OperationExternalDoc
    {
        $this->url = $url;
        return $this;
    }

}