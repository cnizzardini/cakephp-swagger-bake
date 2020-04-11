<?php


namespace SwaggerBake\Lib\OpenApi;

use InvalidArgumentException;

/**
 * Class Path
 * @todo implement $ref
 * @see https://swagger.io/specification/
 */
class Path
{
    private $summary = '';
    private $type = '';
    private $path = '';
    private $tags = [];
    private $operationId = '';
    private $parameters = [];
    private $requestBody;
    private $responses = [];
    private $security = [];
    private $deprecated = false;

    public function toArray() : array
    {
        $vars = get_object_vars($this);
        unset($vars['type']);
        unset($vars['path']);

        if (in_array($this->type, ['get','delete'])) {
            unset($vars['requestBody']);
        }

        return $vars;
    }

    /**
     * @return string
     */
    public function getSummary(): string
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     * @return Path
     */
    public function setSummary(string $summary): Path
    {
        $this->summary = $summary;
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
     * @param string $type
     * @return Path
     */
    public function setType(string $type): Path
    {
        $type = strtolower($type);
        if (!in_array($type, ['get','put', 'post', 'patch', 'delete'])) {
            throw new InvalidArgumentException("type must be a valid HTTP METHOD, $type given");
        }

        $this->type = $type;
        return $this;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     * @return Path
     */
    public function setTags(array $tags): Path
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperationId(): string
    {
        return $this->operationId;
    }

    /**
     * @param string $operationId
     * @return Path
     */
    public function setOperationId(string $operationId): Path
    {
        $this->operationId = $operationId;
        return $this;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     * @return Path
     */
    public function setParameters(array $parameters): Path
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @param Parameter $parameter
     * @return Path
     */
    public function pushParameter(Parameter $parameter): Path
    {
        $this->parameters[] = $parameter;
        return $this;
    }

    /**
     * @return array
     */
    public function getRequestBody() : array
    {
        return $this->requestBody;
    }

    /**
     * @param RequestBody $requestBody
     * @return Path
     */
    public function setRequestBody(RequestBody $requestBody) : Path
    {
        $this->requestBody = $requestBody->toArray();
        return $this;
    }

    /**
     * @return array
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    /**
     * @param array $array
     * @return Path
     */
    public function setResponses(array $array) : Path
    {
        $this->responses = $array;
        return $this;
    }

    /**
     * @param Response $response
     * @return Path
     */
    public function pushResponse(Response $response): Path
    {
        $code = $response->getCode();
        $this->responses[$code] = $response->toArray();
        return $this;
    }

    /**
     * @return array
     */
    public function getSecurity(): array
    {
        return $this->security;
    }

    /**
     * @param array $security
     * @return Path
     */
    public function setSecurity(array $security): Path
    {
        $this->security = $security;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDeprecated(): bool
    {
        return $this->deprecated;
    }

    /**
     * @param bool $deprecated
     * @return Path
     */
    public function setDeprecated(bool $deprecated): Path
    {
        $this->deprecated = $deprecated;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return Path
     */
    public function setPath(string $path): Path
    {
        $this->path = $path;
        return $this;
    }
}
