<?php

namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

/**
 * Class Schema
 * @package SwaggerBake\Lib\OpenApi
 * @see https://swagger.io/docs/specification/data-models/
 */
class Schema implements JsonSerializable
{
    /** @var string */
    private $name = '';

    /** @var string */
    private $description = '';

    /** @var string */
    private $type = '';

    /** @var string[] */
    private $required = [];

    /** @var SchemaProperty[] */
    private $properties = [];

    /** @var string[] */
    private $items = [];

    /** @var array  */
    private $oneOf = [];

    /** @var array  */
    private $anyOf = [];

    /** @var array  */
    private $allOf = [];

    /** @var array  */
    private $enum = [];

    /**
     * @return array
     */
    public function toArray() : array
    {
        $vars = get_object_vars($this);
        unset($vars['name']);
        if (empty($vars['required'])) {
            unset($vars['required']);
        } else {
            // must stay in this order to prevent https://github.com/cnizzardini/cakephp-swagger-bake/issues/30
            $vars['required'] = array_values(array_unique($vars['required']));
        }

        foreach (['properties', 'items', 'oneOf', 'anyOf', 'allOf', 'enum'] as $v) {
            if (empty($vars[$v])) {
                unset($vars[$v]);
            }
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Schema
     */
    public function setName(string $name): Schema
    {
        $this->name = $name;
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
     * @return Schema
     */
    public function setType(string $type): Schema
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return array
     */
    public function getRequired(): array
    {
        return $this->required;
    }

    /**
     * @param string[] $required
     * @return Schema
     */
    public function setRequired(array $required): Schema
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @param string $propertyName
     * @return Schema
     */
    public function pushRequired(string $propertyName): Schema
    {
        $this->required[$propertyName] = $propertyName;
        return $this;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param SchemaProperty[] $properties
     * @return Schema
     */
    public function setProperties(array $properties): Schema
    {
        $this->properties = [];
        foreach ($properties as $property) {
            $this->pushProperty($property);
        }

        return $this;
    }

    /**
     * @param SchemaProperty $property
     * @return Schema
     */
    public function pushProperty(SchemaProperty $property): Schema
    {
        $this->properties[$property->getName()] = $property;

        if ($property->isRequired()) {
            $this->required[$property->getName()] = $property->getName();
        } else if(isset($this->required[$property->getName()])) {
            unset($this->required[$property->getName()]);
        }

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
     * @return Schema
     */
    public function setDescription(string $description): Schema
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param string[] $items
     * @return Schema
     */
    public function setItems(array $items): Schema
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @return array
     */
    public function getOneOf(): array
    {
        return $this->oneOf;
    }

    /**
     * @param array $oneOf
     * @return Schema
     */
    public function setOneOf(array $oneOf): Schema
    {
        $this->oneOf = $oneOf;
        return $this;
    }

    /**
     * @return array
     */
    public function getAnyOf(): array
    {
        return $this->anyOf;
    }

    /**
     * @param array $anyOf
     * @return Schema
     */
    public function setAnyOf(array $anyOf): Schema
    {
        $this->anyOf = $anyOf;
        return $this;
    }

    /**
     * @return array
     */
    public function getAllOf(): array
    {
        return $this->allOf;
    }

    /**
     * @param array $allOf
     * @return Schema
     */
    public function setAllOf(array $allOf): Schema
    {
        $this->allOf = $allOf;
        return $this;
    }

    /**
     * @return array
     */
    public function getEnum(): array
    {
        return $this->enum;
    }

    /**
     * @param array $enum
     * @return Schema
     */
    public function setEnum(array $enum): Schema
    {
        $this->enum = $enum;
        return $this;
    }
}