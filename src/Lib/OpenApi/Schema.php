<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;
use SwaggerBake\Lib\Attribute\OpenApiSchema;
use SwaggerBake\Lib\Utility\ArrayUtility;

/**
 * Class Schema
 *
 * @package SwaggerBake\Lib\OpenApi
 * @see https://swagger.io/docs/specification/data-models/
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Schema implements JsonSerializable, SchemaInterface
{
    use SchemaTrait;

    private ?string $title = null;

    /**
     * @var string[]
     */
    private array $required = [];

    /**
     * A mixed array of Schema and SchemaProperty
     *
     * @var array
     */
    private array $properties = [];

    private ?string $refEntity = null;

    /**
     * @var string[]
     */
    private array $items = [];

    /**
     * @var array
     */
    private array $oneOf = [];

    /**
     * @var array
     */
    private array $anyOf = [];

    /**
     * @var array
     */
    private array $allOf = [];

    /**
     * @var array
     */
    private array $not = [];

    private ?Xml $xml = null;

    private bool $isPublic = true;

    private int $visibility = OpenApiSchema::VISIBILE_DEFAULT;

    /**
     * The openapi ref location (e.g. #/components/schemas/Model)
     *
     * @var string|null
     */
    private ?string $refPath = null;

    /**
     * @return array
     */
    public function toArray(): array
    {
        $vars = get_object_vars($this);

        // always unset
        $vars = ArrayUtility::removeKeysMatching($vars, ['name','refEntity','isPublic', 'refPath','visibility']);

        // must stay in this order to prevent https://github.com/cnizzardini/cakephp-swagger-bake/issues/30
        $vars['required'] = array_values(array_unique($vars['required']));

        if (!empty($this->refEntity)) {
            $vars['$ref'] = $this->refEntity;
        }

        // remove null or empty properties to avoid swagger.json clutter
        $vars = ArrayUtility::removeEmptyVars(
            $vars,
            ['title','properties','items','oneOf','anyOf','allOf','not','enum','format','type','xml','required']
        );

        // remove null properties only
        $vars = ArrayUtility::removeNullValues($vars, ['description']);

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
     * Defines a vendor property, such as `x-my-property`
     *
     * @param string $name name of the attribute
     * @param mixed $value value
     * @return $this
     */
    public function setVendorProperty(string $name, mixed $value)
    {
        $this->{$name} = $value;

        return $this;
    }

    /**
     * @param string $name name of the attribute
     * @return mixed
     */
    public function getVendorProperty(string $name): mixed
    {
        return $this->{$name} ?? null;
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
     * @param \SwaggerBake\Lib\OpenApi\Schema[]|\SwaggerBake\Lib\OpenApi\SchemaProperty[] $properties A mixed array of Schema and SchemaProperty
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
     * @param \SwaggerBake\Lib\OpenApi\SchemaInterface $property instance of SchemaInterface
     * @return $this
     */
    public function pushProperty(SchemaInterface $property)
    {
        $this->properties[$property->getName()] = $property;

        if ($property instanceof SchemaProperty && $property->isRequired()) {
            $this->required[$property->getName()] = $property->getName();
        } elseif (isset($this->required[$property->getName()])) {
            unset($this->required[$property->getName()]);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRefEntity(): ?string
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
     * @param array $items Items
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
     * @param array $oneOf One Of e.g. [['$ref' => '#']]
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
     * @param array $anyOf Any Of e.g. [['$ref' => '#']]
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
     * @param array $allOf All Of e.g. [['$ref' => '#']]
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
     * @param array $not Not e.g. [['$ref' => '#']]
     * @return $this
     */
    public function setNot(array $not)
    {
        $this->not = $not;

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
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    /**
     * @param bool $isPublic indicates visibility
     * @return $this
     */
    public function setIsPublic(bool $isPublic)
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * @return int
     */
    public function getVisibility(): int
    {
        return $this->visibility;
    }

    /**
     * @param int $visibility See OpenApiSchema class constants
     * @return $this
     */
    public function setVisibility(int $visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRefPath(): ?string
    {
        return $this->refPath;
    }

    /**
     * @param string|null $refPath the openapi ref location (e.g. #/components/schemas/Model)
     * @return $this
     */
    public function setRefPath(?string $refPath)
    {
        $this->refPath = $refPath;

        return $this;
    }
}
