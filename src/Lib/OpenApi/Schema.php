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
    public const SCHEMA = '#/x-swagger-bake/components/schemas/';

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
    private $description;

    /**
     * @var string
     */
    private $type = '';

    /**
     * @var string[]
     */
    private $required = [];

    /**
     * A mixed array of Schema and SchemaProperty
     *
     * @var array
     */
    private $properties = [];

    /**
     * @var string
     */
    private $refEntity;

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
     * @var bool
     */
    private $isVisible = true;

    /**
     * @return array
     */
    public function toArray(): array
    {
        $vars = get_object_vars($this);

        // always unset
        foreach (['name','refEntity','isVisible'] as $v) {
            unset($vars[$v]);
        }

        if (empty($vars['required'])) {
            unset($vars['required']);
        } else {
            // must stay in this order to prevent https://github.com/cnizzardini/cakephp-swagger-bake/issues/30
            $vars['required'] = array_values(array_unique($vars['required']));
        }

        if (!empty($this->refEntity)) {
            $vars['$ref'] = $this->refEntity;
        }

        // remove null or empty properties to avoid swagger.json clutter
        foreach (['title','properties','items','oneOf','anyOf','allOf','not','enum','format','type','xml'] as $v) {
            if (array_key_exists($v, $vars) && (empty($vars[$v]) || is_null($vars[$v]))) {
                unset($vars[$v]);
            }
        }

        // remove null properties only
        foreach (['description'] as $v) {
            if (array_key_exists($v, $vars) && is_null($vars[$v])) {
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
     * Defines a vendor property, such as `x-my-property`
     *
     * @param string $name name of the attribute
     * @param mixed $value value
     * @return $this
     */
    public function setVendorProperty(string $name, $value)
    {
        $this->{$name} = $value;

        return $this;
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
     * A mixed array of Schema and SchemaProperty
     *
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param array $properties A mixed array of Schema and SchemaProperty
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
     * @param mixed $property instance of SchemaProperty or Schema
     * @return $this
     */
    public function pushProperty($property)
    {
        if (!$property instanceof SchemaProperty && !$property instanceof Schema) {
            throw new \InvalidArgumentException(
                'Must be instance of SchemaProperty or Schema'
            );
        }

        $this->properties[$property->getName()] = $property;

        if (empty($property->getName())) {
            throw new \LogicException(
                'Name must be set on ' . get_class($property)
            );
        }

        if ($property instanceof Schema) {
            $this->properties[$property->getName()] = $property;

            return $this;
        }

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
     * @param ?string $description Description
     * @return $this
     */
    public function setDescription(?string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getRefEntity(): string
    {
        return $this->refEntity;
    }

    /**
     * @param string $refEntity Reference YAML schema such as #/components/schema/MyEntity
     * @return $this
     */
    public function setRefEntity(string $refEntity)
    {
        $this->refEntity = $refEntity;

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
     * @return string|null
     */
    public function getFormat(): ?string
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

    /**
     * @return string
     */
    public function getWriteSchemaName(): string
    {
        return $this->name . '-Write';
    }

    /**
     * @return string
     */
    public function getAddSchemaName(): string
    {
        return $this->name . '-Add';
    }

    /**
     * @return string
     */
    public function getEditSchemaName(): string
    {
        return $this->name . '-Edit';
    }

    /**
     * @return string
     */
    public function getReadSchemaName(): string
    {
        return $this->name . '-Read';
    }

    /**
     * @return string
     */
    public function getWriteSchemaRef(): string
    {
        return self::SCHEMA . $this->getWriteSchemaName();
    }

    /**
     * @return string
     */
    public function getAddSchemaRef(): string
    {
        return self::SCHEMA . $this->getAddSchemaName();
    }

    /**
     * @return string
     */
    public function getEditSchemaRef(): string
    {
        return self::SCHEMA . $this->getEditSchemaName();
    }

    /**
     * @return string
     */
    public function getReadSchemaRef(): string
    {
        return self::SCHEMA . $this->getReadSchemaName();
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    /**
     * @param bool $isVisible
     * @return Schema
     */
    public function setIsVisible(bool $isVisible): Schema
    {
        $this->isVisible = $isVisible;
        return $this;
    }
}
