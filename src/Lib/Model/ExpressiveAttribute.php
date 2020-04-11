<?php


namespace SwaggerBake\Lib\Model;


class ExpressiveAttribute
{
    private $name = '';
    private $type = '';
    private $default = '';
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
     * @return ExpressiveAttribute
     */
    public function setName(string $name): ExpressiveAttribute
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
     * @return ExpressiveAttribute
     */
    public function setType(string $type): ExpressiveAttribute
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
     * @return ExpressiveAttribute
     */
    public function setDefault(string $default): ExpressiveAttribute
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
     * @return ExpressiveAttribute
     */
    public function setIsPrimaryKey(bool $isPrimaryKey): ExpressiveAttribute
    {
        $this->isPrimaryKey = $isPrimaryKey;
        return $this;
    }
}