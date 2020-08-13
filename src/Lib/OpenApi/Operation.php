<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\OpenApi;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Class Operation
 *
 * @package SwaggerBake\Lib\OpenApi
 * @see https://swagger.io/docs/specification/paths-and-operations/
 */
class Operation implements JsonSerializable
{
    /**
     * @var string
     */
    private $summary = '';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var \SwaggerBake\Lib\OpenApi\OperationExternalDoc|null
     */
    private $externalDocs;

    /**
     * @var string
     */
    private $httpMethod = '';

    /**
     * @var string[]
     */
    private $tags = [];

    /**
     * @var string
     */
    private $operationId = '';

    /**
     * @var \SwaggerBake\Lib\OpenApi\Parameter[]
     */
    private $parameters = [];

    /**
     * @var \SwaggerBake\Lib\OpenApi\RequestBody|null
     */
    private $requestBody;

    /**
     * @var \SwaggerBake\Lib\OpenApi\Response[]
     */
    private $responses = [];

    /**
     * @var \SwaggerBake\Lib\OpenApi\PathSecurity[]
     */
    private $security = [];

    /**
     * @var bool
     */
    private $deprecated = false;

    /**
     * @return array
     */
    public function toArray(): array
    {
        $vars = get_object_vars($this);
        unset($vars['httpMethod']);

        if (in_array($this->httpMethod, ['GET', 'DELETE']) || empty($vars['requestBody'])) {
            unset($vars['requestBody']);
        }
        if (empty($vars['security'])) {
            unset($vars['security']);
        } else {
            $vars['security'] = array_values($vars['security']);
        }
        if (empty($vars['externalDocs'])) {
            unset($vars['externalDocs']);
        }
        if (empty($this->requestBody) || count($this->requestBody->getContent()) === 0) {
            unset($vars['requestBody']);
        }
        if (!empty($vars['parameters'])) {
            $vars['parameters'] = array_values($vars['parameters']);
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
     * @return bool
     */
    public function hasSuccessResponseCode(): bool
    {
        $results = array_filter($this->getResponses(), function ($response) {

            $code = $response->getCode();

            if (!is_numeric($code) && in_array($code, ['2XX', '20X'])) {
                return true;
            }

            return $code >= 200 && $code < 300;
        });

        return count($results) > 0;
    }

    /**
     * Gets httpMethod as UPPERCASE string
     *
     * @return string
     */
    public function getHttpMethod(): string
    {
        return strtoupper($this->httpMethod);
    }

    /**
     * @param string $httpMethod Http method i.e. PUT, POST, PATCH, GET, DELETE
     * @return $this
     */
    public function setHttpMethod(string $httpMethod)
    {
        $httpMethod = strtoupper($httpMethod);
        if (!in_array($httpMethod, ['GET','PUT', 'POST', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'])) {
            throw new InvalidArgumentException("Invalid HTTP METHOD: $httpMethod");
        }

        $this->httpMethod = $httpMethod;

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
     * @param string[] $tags An array of operation tags
     * @return $this
     */
    public function setTags(array $tags)
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
     * @param string $operationId OpenAPI operationId
     * @return $this
     */
    public function setOperationId(string $operationId)
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
     * @param string $type where it is (i.e. query, header, path)
     * @param string $name name of the parameter
     * @return \SwaggerBake\Lib\OpenApi\Parameter
     */
    public function getParameterByTypeAndName($type, $name): Parameter
    {
        if (!in_array($type, Parameter::IN)) {
            throw new InvalidArgumentException(
                "Invalid parameter type `$type`, must be one: " . implode(', ', Parameter::IN)
            );
        }

        $index = "$type:$name";

        if (!isset($this->parameters[$index])) {
            throw new InvalidArgumentException("Parameter $index not found");
        }

        return $this->parameters[$index];
    }

    /**
     * @param \SwaggerBake\Lib\OpenApi\Parameter[] $parameters Array of Parameter objects
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $parameter) {
            $this->pushParameter($parameter);
        }

        return $this;
    }

    /**
     * @param \SwaggerBake\Lib\OpenApi\Parameter $parameter Parameter
     * @return $this
     */
    public function pushParameter(Parameter $parameter)
    {
        $this->parameters[$parameter->getIn() . ':' . $parameter->getName()] = $parameter;

        return $this;
    }

    /**
     * @return \SwaggerBake\Lib\OpenApi\RequestBody|null
     */
    public function getRequestBody(): ?RequestBody
    {
        return $this->requestBody;
    }

    /**
     * @param \SwaggerBake\Lib\OpenApi\RequestBody $requestBody RequestBody
     * @return $this
     */
    public function setRequestBody(RequestBody $requestBody)
    {
        $this->requestBody = $requestBody;

        return $this;
    }

    /**
     * @return \SwaggerBake\Lib\OpenApi\Response[]
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    /**
     * @param string $code Http status code
     * @return \SwaggerBake\Lib\OpenApi\Response|null
     */
    public function getResponseByCode(string $code): ?Response
    {
        return $this->responses[$code] ?? null;
    }

    /**
     * @param \SwaggerBake\Lib\OpenApi\Response[] $array Array of Response objects
     * @return $this
     */
    public function setResponses(array $array)
    {
        $this->responses = $array;

        return $this;
    }

    /**
     * @param \SwaggerBake\Lib\OpenApi\Response $response Response
     * @return $this
     */
    public function pushResponse(Response $response)
    {
        $code = $response->getCode();
        $existingResponse = $this->getResponseByCode($response->getCode());
        if ($this->getResponseByCode($response->getCode())) {
            $content = $existingResponse->getContent() + $response->getContent();
            $existingResponse->setContent($content);
            $this->responses[$code] = $existingResponse;

            return $this;
        }
        $this->responses[$code] = $response;

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
     * @param \SwaggerBake\Lib\OpenApi\PathSecurity[] $pathSecurities Array of PathSecurity
     * @return $this
     */
    public function setSecurity(array $pathSecurities)
    {
        $this->security = [];
        foreach ($pathSecurities as $security) {
            $this->pushSecurity($security);
        }

        return $this;
    }

    /**
     * @param \SwaggerBake\Lib\OpenApi\PathSecurity $security PathSecurity
     * @return $this
     */
    public function pushSecurity(PathSecurity $security)
    {
        $this->security[$security->getName()] = $security;

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
     * @param bool $deprecated Is deprecated?
     * @return $this
     */
    public function setDeprecated(bool $deprecated)
    {
        $this->deprecated = $deprecated;

        return $this;
    }

    /**
     * @return \SwaggerBake\Lib\OpenApi\OperationExternalDoc
     */
    public function getExternalDocs(): OperationExternalDoc
    {
        return $this->externalDocs;
    }

    /**
     * @param \SwaggerBake\Lib\OpenApi\OperationExternalDoc $externalDoc OperationExternalDoc
     * @return $this
     */
    public function setExternalDocs(OperationExternalDoc $externalDoc)
    {
        $this->externalDocs = $externalDoc;

        return $this;
    }

    /**
     * @return string
     */
    public function getSummary(): string
    {
        return $this->summary;
    }

    /**
     * @param string $summary Summary
     * @return $this
     */
    public function setSummary(string $summary)
    {
        $this->summary = $summary;

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
     * @param string $description Description
     * @return $this
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }
}
