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
     * @param array<\SwaggerBake\Lib\OpenApi\Operation> $operations An array of OpenApi Operations
     * @param string|null $ref An optional OpenAPI path $ref
     * @param string|null $summary An optional short summary
     * @param string|null $description An optional description
     * @param array<string> $tags Sets the tag for all operations in the path. Tags set on individual operations will take
     *  precedence.
     */
    public function __construct(
        private string $resource,
        private array $operations = [],
        private ?string $ref = null,
        private ?string $summary = null,
        private ?string $description = null,
        private array $tags = [],
    ) {
        $this->setOperations($operations);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $vars = get_object_vars($this);
        $vars = ArrayUtility::removeKeysMatching($vars, ['resource', 'operations', 'ref', 'tags']);

        // remove items if null to reduce JSON clutter
        $vars = ArrayUtility::removeNullValues($vars, ['summary', 'description']);

        $operations = [];
        foreach ($this->getOperations() as $operation) {
            $operations[strtolower($operation->getHttpMethod())] = $operation;
        }

        uasort($operations, function (Operation $a, Operation $b) {
            return $a->getSortOrder() < $b->getSortOrder() ? -1 : 1;
        });

        $vars += $operations;

        if ($this->ref !== null) {
            $vars['$ref'] = $this->ref;
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
     * @return array<\SwaggerBake\Lib\OpenApi\Operation>
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * @param array<\SwaggerBake\Lib\OpenApi\Operation> $operations Array of Operation
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

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array<string> $tags A list of OpenApi tags
     * @return $this
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;

        return $this;
    }
}
