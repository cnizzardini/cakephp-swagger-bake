<?php


namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

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
            $vars['required'] = array_values($vars['required']);
        }
        if (empty($vars['properties'])) {
            unset($vars['properties']);
        }
        if (empty($vars['items'])) {
            unset($vars['items']);
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
     * @param array $properties
     * @return Schema
     */
    public function setProperties(array $properties): Schema
    {
        $this->properties = $properties;
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


}