<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

/**
 * Class Schema
 *
 * @package SwaggerBake\Lib\OpenApi
 * @see https://swagger.io/docs/specification/data-models/
 */
class Schema implements JsonSerializable
{
    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var string
     */
    private $type = '';

    /**
     * @var string[]
     */
    private $required = [];

    /**
     * @var \SwaggerBake\Lib\OpenApi\SchemaProperty[]
     */
    private $properties = [];

    /**
     * @var string[]
     */
    private $items = [];

    /**
     * @var array
     */
    private $oneOf = [];

    /**
     * @var array
     */
    private $anyOf = [];

    /**
     * @var array
     */
    private $allOf = [];

    /**
     * @var array
     */
    private $not = [];

    /**
     * @var array
     */
    private $enum = [];

    /**
     * @var string
     */
    private $format;

    /**
     * @var \SwaggerBake\Lib\OpenApi\Xml|null
     */
    private $xml;

    /**
     * @return array
     */
    public function toArray(): array
    {
        $vars = get_object_vars($this);
        unset($vars['name']);

        if (empty($vars['required'])) {
            unset($vars['required']);
        } else {
            // must stay in this order to prevent https://github.com/cnizzardini/cakephp-swagger-bake/issues/30
            $vars['required'] = array_values(array_unique($vars['required']));
        }

        // remove empty properties to avoid swagger.json clutter
        foreach (['title','properties','items','oneOf','anyOf','allOf','not','enum','format','type', 'xml'] as $v) {
            if (array_key_exists($v, $vars) && (empty($vars[$v]) || is_null($vars[$v]))) {
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
     * @param string $name Name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title Title
     * @return $this
     */
    public function setTitle(?string $title)
    {
        $this->title = $title;

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
     * @param string $type Type
     * @return $this
     */
    public function setType(string $type)
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
     * @param string[] $required Required
     * @return $this
     */
    public function setRequired(array $required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @param string $propertyName Property name
     * @return $this
     */
    public function pushRequired(string $propertyName)
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
     * @param \SwaggerBake\Lib\OpenApi\SchemaProperty[] $properties Array of SchemaProperty
     * @return $this
     */
    public function setProperties(array $properties)
    {
        $this->properties = [];
        foreach ($properties as $property) {
            $this->pushProperty($property);
        }

        return $this;
    }

    /**
     * @param \SwaggerBake\Lib\OpenApi\SchemaProperty $property SchemaProperty
     * @return $this
     */
    public function pushProperty(SchemaProperty $property)
    {
        $this->properties[$property->getName()] = $property;

        if ($property->isRequired()) {
            $this->required[$property->getName()] = $property->getName();
        } elseif (isset($this->required[$property->getName()])) {
            unset($this->required[$property->getName()]);
        }

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
     * @param string $description Description
     * @return $this
     */
    public function setDescription(string $description)
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
     * @param string[] $items Items
     * @return $this
     */
    public function setItems(array $items)
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
     * @param array $oneOf One Of
     * @return $this
     */
    public function setOneOf(array $oneOf)
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
     * @param array $anyOf Any Of
     * @return $this
     */
    public function setAnyOf(array $anyOf)
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
     * @param array $allOf All Of
     * @return $this
     */
    public function setAllOf(array $allOf)
    {
        $this->allOf = $allOf;

        return $this;
    }

    /**
     * @return array
     */
    public function getNot(): array
    {
        return $this->not;
    }

    /**
     * @param array $not Not
     * @return $this
     */
    public function setNot(array $not)
    {
        $this->not = $not;

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
     * @param array $enum Enumerated list
     * @return $this
     */
    public function setEnum(array $enum)
    {
        $this->enum = $enum;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param string $format Format
     * @return $this
     */
    public function setFormat(string $format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @return \SwaggerBake\Lib\OpenApi\Xml|null
     */
    public function getXml(): ?Xml
    {
        return $this->xml;
    }

    /**
     * @param \SwaggerBake\Lib\OpenApi\Xml|null $xml Xml
     * @return $this
     */
    public function setXml(?Xml $xml)
    {
        $this->xml = $xml;

        return $this;
    }
}
