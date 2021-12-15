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

    /**
     * @var string|null
     */
    private $title;

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
     * @var \SwaggerBake\Lib\OpenApi\Xml|null
     */
    private $xml;

    /**
     * @var bool
     */
    private $isPublic = true;

    private int $visibility = OpenApiSchema::VISIBILE_DEFAULT;

    /**
     * The openapi ref location (e.g. #/components/schemas/Model)
     *
     * @var string|null
     */
    private $refPath;

    /**
     * @return array
     */
    public function toArray(): array
    {
        $vars = get_object_vars($this);

        // always unset
        $vars = ArrayUtility::removeKeysMatching($vars, ['name','refEntity','isPublic', 'refPath','visibility']);

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
        $vars = ArrayUtility::removeEmptyAndNullValues(
            $vars,
            ['title','properties','items','oneOf','anyOf','allOf','not','enum','format','type','xml']
        );

        $vars = ArrayUtility::removeEmptyAndNullValues(
            $vars,
            ['title','properties','items','oneOf','anyOf','allOf','not','enum','format','type','xml']
        );

        // remove null properties only
        foreach (['description'] as $v) {
            if (array_key_exists($v, $vars) && is_null($vars[$v])) {
                unset($vars[$v]);
            }
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
     * @param \SwaggerBake\Lib\OpenApi\SchemaInterface $property instance of SchemaInterface
     * @return $this
     */
    public function pushProperty(SchemaInterface $property)
    {
        /*        if (empty($property->getName())) {
                    throw new \LogicException(
                        'Name must be set on ' . get_class($property)
                    );
                }*/

        $this->properties[$property->getName()] = $property;

        if ($property instanceof SchemaProperty && $property->isRequired()) {
            $this->required[$property->getName()] = $property->getName();
        } elseif (isset($this->required[$property->getName()])) {
            unset($this->required[$property->getName()]);
        }

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
     * @param string $refPath the openapi ref location (e.g. #/components/schemas/Model)
     * @return $this
     */
    public function setRefPath(string $refPath)
    {
        $this->refPath = $refPath;

        return $this;
    }
}
