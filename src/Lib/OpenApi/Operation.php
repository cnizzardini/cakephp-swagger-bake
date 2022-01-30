<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\OpenApi;

use Cake\Routing\Route\Route;
use InvalidArgumentException;
use JsonSerializable;
use SwaggerBake\Lib\Utility\ArrayUtility;

/**
 * Class Operation
 *
 * @package SwaggerBake\Lib\OpenApi
 * @see https://swagger.io/docs/specification/paths-and-operations/
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Operation implements JsonSerializable
{
    /**
     * @param string $operationId OpenApi Operation Id
     * @param string $httpMethod The HTTP method
     * @param string|null $summary An optional short summary
     * @param string|null $description An optional description
     * @param array $tags OpenApi tags
     * @param \SwaggerBake\Lib\OpenApi\OperationExternalDoc|null $externalDocs Optional External Documentation
     * @param \SwaggerBake\Lib\OpenApi\RequestBody|null $requestBody Optional request body
     * @param array $parameters A mixed array of OpenApi Parameter and/or OpenApi $ref
     * @param \SwaggerBake\Lib\OpenApi\Response[] $responses Array of OpenApi Response
     * @param \SwaggerBake\Lib\OpenApi\PathSecurity[] $security Array of OpenApi PathSecurity
     * @param bool $isDeprecated Is this operation deprecated?
     * @param int $sortOrder The sort order, by default uses the order of methods in the controller.
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private string $operationId,
        private string $httpMethod,
        private ?string $summary = null,
        private ?string $description = null,
        private array $tags = [],
        private ?OperationExternalDoc $externalDocs = null,
        private ?RequestBody $requestBody = null,
        private array $parameters = [],
        private array $responses = [],
        private array $security = [],
        private bool $isDeprecated = false,
        private int $sortOrder = 100
    ) {
        $this->setHttpMethod($httpMethod);
        $this->setParameters($parameters);
        $this->setSecurity($security);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $vars = get_object_vars($this);
        $vars = ArrayUtility::convertNullToEmptyString($vars, ['summary','description']);
        $vars['deprecated'] = $vars['isDeprecated'];
        $vars = ArrayUtility::removeKeysMatching($vars, ['isDeprecated', 'httpMethod', 'sortOrder']);

        // remove request body got GET and DELETE
        if (in_array($this->httpMethod, ['GET', 'DELETE']) || empty($vars['requestBody'])) {
            unset($vars['requestBody']);
        }

        // remove openapi properties that are not required (if they are empty)
        $vars = ArrayUtility::removeEmptyVars($vars, ['security', 'externalDocs']);

        // remove openapi properties matching their default value
        $vars = ArrayUtility::removeValuesMatching($vars, ['deprecated' => false]);

        // security should be numerically indexed
        if (isset($vars['security'])) {
            $vars['security'] = array_values($vars['security']);
        }

        // if request body content is empty remove it
        if ($this->requestBody && count($this->requestBody->getContent()) === 0) {
            unset($vars['requestBody']);
        }

        // parameters should be numerically indexed
        if (!empty($vars['parameters'])) {
            $vars['parameters'] = array_values($vars['parameters']);
        }

        return $vars;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
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
        $this->httpMethod = strtoupper($httpMethod);
        if (!in_array($this->httpMethod, Route::VALID_METHODS)) {
            throw new InvalidArgumentException("Invalid HTTP method given: `$this->httpMethod`");
        }

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
     * @return \SwaggerBake\Lib\OpenApi\Parameter[]
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
    public function getParameterByTypeAndName(string $type, string $name): Parameter
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
     * @param string $ref a ref string (e.g. '#/components/parameters/paginatorPage')
     * @return $this
     */
    public function pushRefParameter(string $ref)
    {
        $this->parameters[$ref] = ['$ref' => $ref];

        return $this;
    }

    /**
     * @param \SwaggerBake\Lib\OpenApi\Parameter $parameter Parameter
     * @return $this
     */
    public function pushParameter(Parameter $parameter)
    {
        if (!empty($parameter->getRef())) {
            $name = preg_replace('/^\W/', '', str_replace('/', '-', $parameter->getRef()));
            $name = substr($name, 1);
        } else {
            $name = $parameter->getName();
        }

        $this->parameters[$parameter->getIn() . ':' . $name] = $parameter;

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

        if ($existingResponse) {
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
        return $this->isDeprecated;
    }

    /**
     * @param bool $deprecated Is deprecated?
     * @return $this
     */
    public function setDeprecated(bool $deprecated)
    {
        $this->isDeprecated = $deprecated;

        return $this;
    }

    /**
     * @return \SwaggerBake\Lib\OpenApi\OperationExternalDoc|null
     */
    public function getExternalDocs(): ?OperationExternalDoc
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
     * @return string|null
     */
    public function getSummary(): ?string
    {
        return $this->summary;
    }

    /**
     * @param ?string $summary Summary
     * @return $this
     */
    public function setSummary(?string $summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param ?string $description Description
     * @return $this
     */
    public function setDescription(?string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder Where the operation appears in OpenAPI result
     * @return $this
     */
    public function setSortOrder(int $sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
