<?php

namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

/**
 * Class PathSecurity
 * @package SwaggerBake\Lib\OpenApi
 * @see https://swagger.io/docs/specification/authentication/
 */
class PathSecurity implements JsonSerializable
{
    /** @var string  */
    private $name = '';

    /** @var string[]  */
    private $scopes = [];

    /**
     * @return array|array[]
     */
    public function toArray() : array
    {
        return [
            $this->name => array_values($this->scopes)
        ];
    }

    /**
     * @return array|array[]|mixed
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return PathSecurity
     */
    public function setName(string $name): PathSecurity
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @param array $scopes
     * @return PathSecurity
     */
    public function setScopes(array $scopes): PathSecurity
    {
        $this->scopes = $scopes;
        return $this;
    }
}