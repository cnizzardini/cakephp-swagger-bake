<?php


namespace SwaggerBake\Lib\OpenApi;

use LogicException;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Class Parameter
 * @package SwaggerBake\Lib\OpenApi
 * @see https://swagger.io/docs/specification/describing-parameters/
 */
class Parameter implements JsonSerializable
{
    /** @var string **/
    private $name = '';

    /** @var string **/
    private $in = '';

    /** @var string **/
    private $description = '';

    /** @var bool **/
    private $required = false;

    /** @var Schema **/
    private $schema;

    /** @var bool **/
    private $deprecated = false;

    /** @var bool **/
    private $allowEmptyValue = false;

    /** @var bool **/
    private $explode = false;

    /** @var string **/
    private $style = '';

    /** @var bool **/
    private $allowReserved = false;

    /** @var mixed **/
    private $example = '';

    public function toArray() : array
    {
        if (empty($this->in)) {
            throw new LogicException('Parameter::in is required for ' . $this->name);
        }

        return get_object_vars($this);
    }

    public function jsonSerialize()
    {
        $vars = $this->toArray();

        if ($vars['in'] !== 'query') {
            unset($vars['allowReserved']);
        }

        foreach (['style','description','schema','example'] as $property) {
            if (empty($vars[$property])) {
                unset($vars[$property]);
            }
        }

        return $vars;
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
     * @return Parameter
     */
    public function setName(string $name): Parameter
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getIn(): string
    {
        return $this->in;
    }

    /**
     * @param string $in
     * @return Parameter
     */
    public function setIn(string $in): Parameter
    {
        $in = strtolower($in);
        if (!in_array($in, ['query','cookie','header','path','body'])) {
            throw new InvalidArgumentException("Invalid type for in. Given $in");
        }
        $this->in = $in;
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
     * @param string $description
     * @return Parameter
     */
    public function setDescription(string $description): Parameter
    {
        $this->description = $description;
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
     * @return Parameter
     */
    public function setRequired(bool $required): Parameter
    {
        $this->required = $required;
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
     * @param bool $deprecated
     * @return Parameter
     */
    public function setDeprecated(bool $deprecated): Parameter
    {
        $this->deprecated = $deprecated;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowEmptyValue(): bool
    {
        return $this->allowEmptyValue;
    }

    /**
     * @param bool $allowEmptyValue
     * @return Parameter
     */
    public function setAllowEmptyValue(bool $allowEmptyValue): Parameter
    {
        $this->allowEmptyValue = $allowEmptyValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @param Schema $schema
     * @return Parameter
     */
    public function setSchema(Schema $schema) : Parameter
    {
        $this->schema = $schema;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExplode(): bool
    {
        return $this->explode;
    }

    /**
     * @param bool $explode
     * @return Parameter
     */
    public function setExplode(bool $explode): Parameter
    {
        $this->explode = $explode;
        return $this;
    }

    /**
     * @return string
     */
    public function getStyle(): string
    {
        return $this->style;
    }

    /**
     * @param string $style
     * @return Parameter
     */
    public function setStyle(string $style): Parameter
    {
        $this->style = $style;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowReserved(): bool
    {
        return $this->allowReserved;
    }

    /**
     * @param bool $allowReserved
     * @return Parameter
     */
    public function setAllowReserved(bool $allowReserved): Parameter
    {
        $this->allowReserved = $allowReserved;
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
     * @param mixed $example
     * @return Parameter
     */
    public function setExample($example): Parameter
    {
        $this->example = $example;
        return $this;
    }
}