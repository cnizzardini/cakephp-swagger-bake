<?php


namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

class SchemaProperty implements JsonSerializable
{
    /** @var string  */
    private $name = '';

    /** @var string  */
    private $type = '';

    /** @var string  */
    private $format = '';

    /** @var string  */
    private $example = '';

    /** @var bool  */
    private $readOnly = false;

    /** @var bool  */
    private $writeOnly = false;

    /** @var bool  */
    private $required = false;

    public function toArray() : array
    {
        $vars = get_object_vars($this);
        unset($vars['name']);
        unset($vars['required']);
        return $vars;
    }

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
     * @return SchemaProperty
     */
    public function setName(string $name): SchemaProperty
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
     * @return SchemaProperty
     */
    public function setType(string $type): SchemaProperty
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
     * @param string $format
     * @return SchemaProperty
     */
    public function setFormat(string $format): SchemaProperty
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
     * @param bool $readOnly
     * @return SchemaProperty
     */
    public function setReadOnly(bool $readOnly): SchemaProperty
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
     * @param bool $writeOnly
     * @return SchemaProperty
     */
    public function setWriteOnly(bool $writeOnly): SchemaProperty
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
     * @param bool $required
     * @return SchemaProperty
     */
    public function setRequired(bool $required): SchemaProperty
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
     * @param string $example
     * @return SchemaProperty
     */
    public function setExample(string $example): SchemaProperty
    {
        $this->example = $example;
        return $this;
    }
}