<?php


namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

class Response implements JsonSerializable
{
    private $code = 0;
    private $description = '';
    private $schemaRef = '';

    public function toArray() : array
    {
        $vars = get_object_vars($this);
        unset($vars['code']);
        unset($vars['schemaRef']);
        $vars['content']['application/json']['schema']['$ref'] = $this->schemaRef;
        return $vars;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @return Response
     */
    public function setCode(int $code): Response
    {
        $this->code = $code;
        return $this;
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
     * @return Response
     */
    public function setDescription(string $description): Response
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getSchemaRef(): string
    {
        return $this->schemaRef;
    }

    /**
     * @param string|null $schemaRef
     * @return $this
     */
    public function setSchemaRef(?string $schemaRef): Response
    {
        $this->schemaRef = $schemaRef;
        return $this;
    }


}