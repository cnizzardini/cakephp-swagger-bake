<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

/**
 * Class Path
 *
 * @package SwaggerBake\Lib\OpenApi
 * @see https://swagger.io/docs/specification/paths-and-operations/
 */
class Path implements JsonSerializable
{
    /**
     * The endpoint (resource) for the path
     *
     * @var string
     */
    private $resource = '';

    /**
     * @var \SwaggerBake\Lib\OpenApi\Operation[]
     */
    private $operations = [];

    /**
     * @var string|null
     */
    private $ref;

    /**
     * @var string|null
     */
    private $summary;

    /**
     * @var string|null
     */
    private $description;

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
        foreach (['summary', 'description'] as $v) {
            if (is_null($vars[$v])) {
                unset($vars[$v]);
            }
        }

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
