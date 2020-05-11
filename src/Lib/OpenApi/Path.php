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
    /**
     * The endpoint (resource) for the path
     * @var string
     */
    private $resource = '';

    /**
     * Short name of the path
     * @var string
     */
    private $summary = '';

    /**
     * Long description of the path
     * @var string
     */
    private $description = '';

    /**
     * @var Operation[]
     */
    private $operations = [];

    public function toArray(): array
    {
        $vars = get_object_vars($this);
        unset($vars['resource']);
        return $vars;
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
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Path
     */
    public function setDescription(string $description): Path
    {
        $this->description = $description;
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
        $this->operations = $operations;
        return $this;
    }

    /**
     * @param Operation $operation
     * @return $this
     */
    public function pushOperation(Operation $operation) : Path
    {
        $this->operations[$operation->getHttpMethod()] = $operation;
        return $this;
    }
}
