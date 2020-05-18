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
    private $allowEmptyValue = true;

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
        if (empty($vars['description'])) {
            unset($vars['description']);
        }
        if (empty($vars['schema'])) {
            unset($vars['schema']);
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
}