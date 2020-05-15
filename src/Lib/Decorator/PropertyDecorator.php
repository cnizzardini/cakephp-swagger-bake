<?php


namespace SwaggerBake\Lib\Decorator;


class PropertyDecorator
{
    /** @var string  */
    private $name = '';

    /** @var string  */
    private $type = '';

    /** @var string  */
    private $default = '';

    /** @var bool  */
    private $isPrimaryKey = false;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return PropertyDecorator
     */
    public function setName(string $name): PropertyDecorator
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
     * @return PropertyDecorator
     */
    public function setType(string $type): PropertyDecorator
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefault(): string
    {
        return $this->default;
    }

    /**
     * @param string $default
     * @return PropertyDecorator
     */
    public function setDefault(string $default): PropertyDecorator
    {
        $this->default = $default;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPrimaryKey(): bool
    {
        return $this->isPrimaryKey;
    }

    /**
     * @param bool $isPrimaryKey
     * @return PropertyDecorator
     */
    public function setIsPrimaryKey(bool $isPrimaryKey): PropertyDecorator
    {
        $this->isPrimaryKey = $isPrimaryKey;
        return $this;
    }
}