<?php

namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

/**
 * Class Path
 * @package SwaggerBake\Lib\OpenApi
 * @see https://swagger.io/docs/specification/paths-and-operations/
 */
class Path implements JsonSerializable
{
    /**
     * The endpoint (resource) for the path
     * @var string
     */
    private $resource = '';

    /**
     * @var Operation[]
     */
    private $operations = [];

    /** @var string|null */
    private $ref;

    /** @var string|null */
    private $summary;

    /** @var string|null */
    private $description;

    public function toArray(): array
    {
        $vars = get_object_vars($this);
        unset($vars['resource']);
        unset($vars['operations']);
        unset($vars['ref']);

        // remove items if null to reduce JSON clutter
        foreach(['summary', 'description'] as $v) {
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
     * @param string $resource
     * @return Path
     */
    public function setResource(string $resource): Path
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @return Operation[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * @param Operation[] $operations
     * @return Path
     */
    public function setOperations(array $operations): Path
    {
        $this->operations = [];
        foreach ($operations as $operation) {
            $this->pushOperation($operation);
        }
        return $this;
    }

    /**
     * @param Operation $operation
     * @return $this
     */
    public function pushOperation(Operation $operation) : Path
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
     * @param string|null $ref
     * @return Path
     */
    public function setRef(?string $ref): Path
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
     * @param string|null $summary
     * @return Path
     */
    public function setSummary(?string $summary): Path
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
     * @param string|null $description
     * @return Path
     */
    public function setDescription(?string $description): Path
    {
        $this->description = $description;
        return $this;
    }
}
