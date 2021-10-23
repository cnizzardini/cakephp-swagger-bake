<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\OpenApi;

use InvalidArgumentException;
use JsonSerializable;
use SwaggerBake\Lib\Utility\ArrayUtility;

/**
 * Class Parameter
 *
 * @package SwaggerBake\Lib\OpenApi
 * @see https://spec.openapis.org/oas/latest.html#parameter-object
 */
class Parameter implements JsonSerializable
{
    /**
     * The location of the parameter.
     *
     * @var string[]
     */
    public const IN = ['query','cookie','header','path'];

    /**
     * @param string $in Where the parameter exists, see Parameter::IN
     * @param string|null $ref An OpenAPI $ref, required if name is empty or null
     * @param string|null $name Name of the parameter, required if $ref is empty or null
     * @param string|null $description An optional description
     * @param bool $required Is this parameter required?
     * @param \SwaggerBake\Lib\OpenApi\Schema|null $schema An optional Schema
     * @param bool $deprecated Is this parameter deprecated?
     * @param bool $allowEmptyValue Are empty values allowed?
     * @param bool $explode Does this parameter accept a comma-separated list?
     * @param string|null $style See https://spec.openapis.org/oas/latest.html#fixed-fields-9
     * @param bool $allowReserved See https://spec.openapis.org/oas/latest.html#fixed-fields-9
     * @param string|null $example See https://spec.openapis.org/oas/latest.html#fixed-fields-9
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private string $in,
        private ?string $ref = null,
        private ?string $name = null,
        private ?string $description = null,
        private bool $required = false,
        private ?Schema $schema = null,
        private bool $deprecated = false,
        private bool $allowEmptyValue = false,
        private bool $explode = false,
        private ?string $style = null,
        private bool $allowReserved = false,
        private ?string $example = null,
    ) {
        $this->setIn($in);
        if (empty($ref) && empty($name)) {
            throw new InvalidArgumentException('A name or ref is required');
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        $vars = $this->toArray();

        if (!empty($this->ref)) {
            return ['$ref' => $this->ref];
        }

        if ($vars['in'] !== 'query') {
            unset($vars['allowReserved']);
        }

        // remove openapi properties that are not required (if they are empty)
        $vars = ArrayUtility::removeEmptyVars($vars, ['style','description','schema','example','ref']);

        // reduce JSON clutter if these values are equal to their defaults
        return ArrayUtility::removeValuesMatching(
            $vars,
            ['deprecated' => false, 'allowEmptyValue' => false, 'explode' => false, 'allowReserved' => false]
        );
    }

    /**
     * @return string|null
     */
    public function getRef(): ?string
    {
        return $this->ref;
    }

    /**
     * @param string $ref a ref string such (e.g. #/x-my-project/components/parameters/my-header)
     * @return $this
     */
    public function setRef(string $ref)
    {
        $this->ref = $ref;

        return $this;
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
    public function getIn(): string
    {
        return $this->in;
    }

    /**
     * @param string $in In
     * @return $this
     */
    public function setIn(string $in)
    {
        $in = strtolower($in);
        if (!in_array($in, self::IN)) {
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
     * @param string $description Description
     * @return $this
     */
    public function setDescription(string $description)
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
     * @param bool $required Is required
     * @return $this
     */
    public function setRequired(bool $required)
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
    public function isAllowEmptyValue(): bool
    {
        return $this->allowEmptyValue;
    }

    /**
     * @param bool $allowEmptyValue Allow empty
     * @return $this
     */
    public function setAllowEmptyValue(bool $allowEmptyValue)
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
     * @param \SwaggerBake\Lib\OpenApi\Schema $schema Schema
     * @return $this
     */
    public function setSchema(Schema $schema)
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
     * @param bool $explode Explode
     * @return $this
     */
    public function setExplode(bool $explode)
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
     * @param string $style Style
     * @return $this
     */
    public function setStyle(string $style)
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
     * @param bool $allowReserved Allow reserved
     * @return $this
     */
    public function setAllowReserved(bool $allowReserved)
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
     * @param mixed $example Example
     * @return $this
     */
    public function setExample($example)
    {
        $this->example = $example;

        return $this;
    }
}
