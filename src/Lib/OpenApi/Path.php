<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;
use SwaggerBake\Lib\Utility\ArrayUtility;

/**
 * Class Path
 *
 * @package SwaggerBake\Lib\OpenApi
 * @see https://spec.openapis.org/oas/latest.html#paths-object
 */
class Path implements JsonSerializable
{
    /**
     * @param string $resource The resource (base URL), for example: /pets
     * @param \SwaggerBake\Lib\OpenApi\Operation[] $operations An array of OpenApi Operations
     * @param string|null $ref An optional OpenAPI path $ref
     * @param string|null $summary An optional short summary
     * @param string|null $description An optional description
     */
    public function __construct(
        private string $resource,
        private array $operations = [],
        private ?string $ref = null,
        private ?string $summary = null,
        private ?string $description = null,
    ) {
        $this->setOperations($operations);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $vars = get_object_vars($this);
        unset($vars['resource']);
        unset($vars['operations']);
        unset($vars['ref']);

        // remove items if null to reduce JSON clutter
        $vars = ArrayUtility::removeNullValues($vars, ['summary', 'description']);

        foreach ($this->getOperations() as $operation) {
            $vars[strtolower($operation->getHttpMethod())] = $operation;
        }

        if ($this->ref !== null) {
            $vars['$ref'] = $this->ref;
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
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * @param string $resource Resource
     * @return $this
     */
    public function setResource(string $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return \SwaggerBake\Lib\OpenApi\Operation[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * @param \SwaggerBake\Lib\OpenApi\Operation[] $operations Array of Operation
     * @return $this
     */
    public function setOperations(array $operations)
    {
        $this->operations = [];
        foreach ($operations as $operation) {
            $this->pushOperation($operation);
        }

        return $this;
    }

    /**
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @return $this
     */
    public function pushOperation(Operation $operation)
    {
        $httpMethod = strtolower($operation->getHttpMethod());
        $this->operations[$httpMethod] = $operation;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRef(): ?string
    {
        return $this->ref;
    }

    /**
     * @param string|null $ref Ref
     * @return $this
     */
    public function setRef(?string $ref)
    {
        $this->ref = $ref;

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
     * @param string|null $summary Summary
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
     * @param string|null $description Description
     * @return $this
     */
    public function setDescription(?string $description)
    {
        $this->description = $description;

        return $this;
    }
}
