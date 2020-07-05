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
class SchemaProperty implements JsonSerializable
{
    use JsonSchemaTrait;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $type = '';

    /**
     * @var string
     */
    private $format = '';

    /**
     * @var string
     */
    private $example = '';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var bool
     */
    private $readOnly = false;

    /**
     * @var bool
     */
    private $writeOnly = false;

    /**
     * @var bool
     */
    private $required = false;

    /**
     * @var array
     */
    private $enum = [];

    /**
     * @var bool
     */
    private $deprecated = false;

    /**
     * @var bool
     */
    private $requirePresenceOnCreate = false;

    /**
     * @var bool
     */
    private $requirePresenceOnUpdate = false;

    /**
     * @return array
     */
    public function toArray(): array
    {
        $vars = get_object_vars($this);
        // allows remove this items from JSON
        foreach (['name','required','requirePresenceOnCreate','requirePresenceOnUpdate'] as $v) {
            unset($vars[$v]);
        }

        // reduce JSON clutter by removing empty values
        foreach (['example','description','enum'] as $v) {
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
     * @return string
     */
    public function getExample(): string
    {
        return $this->example;
    }

    /**
     * @param string|int $example Example
     * @return $this
     */
    public function setExample($example)
    {
        $this->example = (string)$example;

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
    public function getEnum(): array
    {
        return $this->enum;
    }

    /**
     * @param array $enum Enumerated values
     * @return $this
     */
    public function setEnum(array $enum)
    {
        $this->enum = $enum;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeprecated(): bool
    {
        return $this->deprecated;
    }

    /**
     * @param bool $deprecated Deprecated
     * @return $this
     */
    public function setDeprecated(bool $deprecated)
    {
        $this->deprecated = $deprecated;

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
}
