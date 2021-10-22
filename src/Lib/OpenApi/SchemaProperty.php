<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

/**
 * Class SchemaProperty
 *
 * @package SwaggerBake\Lib\OpenApi
 * @see https://swagger.io/docs/specification/data-models/
 */
class SchemaProperty implements JsonSerializable, SchemaInterface
{
    use JsonSchemaTrait;
    use SchemaTrait;

    /**
     * @var mixed
     */
    private $example;

    private bool $readOnly = false;

    private bool $writeOnly = false;

    private bool $required = false;

    private bool $requirePresenceOnCreate = false;

    private bool $requirePresenceOnUpdate = false;

    private array $items = [];

    private ?string $refEntity = null;

    /**
     * @return array
     */
    public function toArray(): array
    {
        $vars = get_object_vars($this);

        // remove internal properties
        foreach (['name','required','requirePresenceOnCreate','requirePresenceOnUpdate','refEntity'] as $v) {
            unset($vars[$v]);
        }

        if (!empty($this->refEntity)) {
            $vars['$ref'] = $this->refEntity;
        }

        // reduce JSON clutter by removing empty values
        foreach (['example','description','enum','format','items','type'] as $v) {
            if (empty($vars[$v])) {
                unset($vars[$v]);
            }
        }

        // reduce JSON clutter if these values are equal to their defaults
        foreach (['readOnly', 'writeOnly', 'deprecated'] as $name) {
            if ($vars[$name] === false) {
                unset($vars[$name]);
            }
        }

        return $this->removeEmptyVars($vars);
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    /**
     * @param bool $readOnly Read Only
     * @return $this
     */
    public function setReadOnly(bool $readOnly)
    {
        $this->readOnly = $readOnly;

        return $this;
    }

    /**
     * @return bool
     */
    public function isWriteOnly(): bool
    {
        return $this->writeOnly;
    }

    /**
     * @param bool $writeOnly Write Only
     * @return $this
     */
    public function setWriteOnly(bool $writeOnly)
    {
        $this->writeOnly = $writeOnly;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required Required
     * @return $this
     */
    public function setRequired(bool $required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExample()
    {
        return $this->example;
    }

    /**
     * @param mixed $example An example value
     * @return $this
     */
    public function setExample($example)
    {
        $this->example = $example;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequirePresenceOnCreate(): bool
    {
        return $this->requirePresenceOnCreate;
    }

    /**
     * @param bool $requirePresenceOnCreate Require presence on create
     * @return $this
     */
    public function setRequirePresenceOnCreate(bool $requirePresenceOnCreate)
    {
        $this->requirePresenceOnCreate = $requirePresenceOnCreate;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequirePresenceOnUpdate(): bool
    {
        return $this->requirePresenceOnUpdate;
    }

    /**
     * @param bool $requirePresenceOnUpdate Require presence on update
     * @return $this
     */
    public function setRequirePresenceOnUpdate(bool $requirePresenceOnUpdate)
    {
        $this->requirePresenceOnUpdate = $requirePresenceOnUpdate;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTypeScalar(): bool
    {
        return in_array($this->type, ['integer','string','float','boolean','bool','int']);
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array $items array of items
     * @return $this
     */
    public function setItems(array $items)
    {
        $this->items = $items;

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
}
