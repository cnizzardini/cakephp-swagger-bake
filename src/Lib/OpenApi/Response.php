<?php

namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

/**
 * Class Response
 * @package SwaggerBake\Lib\OpenApi
 * @see https://swagger.io/docs/specification/describing-responses/
 */
class Response implements JsonSerializable
{
    /** @var string  */
    private $code;

    /** @var string  */
    private $description = '';

    /** @var Content[]  */
    private $content = [];

    /**
     * @return array
     */
    public function toArray() : array
    {
        $vars = get_object_vars($this);
        unset($vars['code']);
        if (empty($vars['content'])) {
            unset($vars['content']);
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
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Response
     */
    public function setCode(string $code): Response
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
     * @return Content[]
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * Sets the array of Content[]
     *
     * @param Content[] $contents
     * @return Response
     */
    public function setContent(array $contents): Response
    {
        $this->content = $contents;
        return $this;
    }

    /**
     * Appends to array of Content[]
     *
     * @param Content $content
     * @return Response
     */
    public function pushContent(Content $content): Response
    {
        $this->content[$content->getMimeType()] = $content;
        return $this;
    }
}