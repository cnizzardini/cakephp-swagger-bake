<?php


namespace SwaggerBake\Lib\Model;


class ExpressiveModel
{
    /** @var string  */
    private $name = '';

    /** @var array  */
    private $attributes = [];

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ExpressiveModel
     */
    public function setName(string $name): ExpressiveModel
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return ExpressiveModel
     */
    public function setAttributes(array $attributes): ExpressiveModel
    {
        $this->attributes = $attributes;
        return $this;
    }
}