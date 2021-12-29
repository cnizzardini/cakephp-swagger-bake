<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\OpenApi;

trait JsonSchemaTrait
{
    protected ?string $title = null;
    protected mixed $default;
    protected bool $nullable = false;
    protected ?float $multipleOf = null;
    protected ?float $minimum = null;
    protected ?float $maximum = null;
    protected bool $exclusiveMinimum = false;
    protected bool $exclusiveMaximum = false;
    protected ?int $minLength = null;
    protected ?int $maxLength = null;
    protected ?string $pattern = null;
    protected ?int $minItems = null;
    protected ?int $maxItems = null;
    protected bool $uniqueItems = false;
    protected ?int $minProperties = null;
    protected ?int $maxProperties = null;
    private bool $deprecated = false;

    /**
     * @param array $vars Object properties as a key-value pair
     * @return array
     */
    public function removeEmptyVars(array $vars): array
    {
        $empties = [
            'title','default','multipleOf','minimum','maximum','exclusiveMinimum','exclusiveMaximum','minLength',
            'maxLength','pattern','minItems','maxItems','uniqueItems','minProperties','maxProperties','nullable',
        ];

        foreach ($vars as $name => $value) {
            if (in_array($name, $empties) && (empty($value) || $value == null)) {
                unset($vars[$name]);
            }
        }

        return $vars;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title Title
     * @return $this
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * @param mixed $default Default
     * @return $this
     */
    public function setDefault(mixed $default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @param bool $nullable Nullable
     * @return $this
     */
    public function setNullable(bool $nullable)
    {
        $this->nullable = $nullable;

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
     * @return float|null
     */
    public function getMultipleOf(): ?float
    {
        return $this->multipleOf;
    }

    /**
     * @param float $multipleOf multipleOf
     * @return $this
     */
    public function setMultipleOf(float $multipleOf)
    {
        $this->multipleOf = $multipleOf;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getMaximum(): ?float
    {
        return $this->maximum;
    }

    /**
     * @param float $maximum Maximum
     * @return $this
     */
    public function setMaximum(float $maximum)
    {
        $this->maximum = $maximum;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExclusiveMaximum(): bool
    {
        return $this->exclusiveMaximum;
    }

    /**
     * @param bool $exclusiveMaximum ExclusiveMaximum
     * @return $this
     */
    public function setExclusiveMaximum(bool $exclusiveMaximum)
    {
        $this->exclusiveMaximum = $exclusiveMaximum;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getMinimum(): ?float
    {
        return $this->minimum;
    }

    /**
     * @param float $minimum Minimum
     * @return $this
     */
    public function setMinimum(float $minimum)
    {
        $this->minimum = $minimum;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExclusiveMinimum(): bool
    {
        return $this->exclusiveMinimum;
    }

    /**
     * @param bool $exclusiveMinimum Exclusive Minimum
     * @return $this
     */
    public function setExclusiveMinimum(bool $exclusiveMinimum)
    {
        $this->exclusiveMinimum = $exclusiveMinimum;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    /**
     * @param int $maxLength MaxLength
     * @return $this
     */
    public function setMaxLength(int $maxLength)
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMinLength(): ?int
    {
        return $this->minLength;
    }

    /**
     * @param int $minLength MinLength
     * @return $this
     */
    public function setMinLength(int $minLength)
    {
        $this->minLength = $minLength;

        return $this;
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @param string $pattern Pattern
     * @return $this
     */
    public function setPattern(string $pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxItems(): ?int
    {
        return $this->maxItems;
    }

    /**
     * @param int $maxItems MaxItems
     * @return $this
     */
    public function setMaxItems(int $maxItems)
    {
        $this->maxItems = $maxItems;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMinItems(): ?int
    {
        return $this->minItems;
    }

    /**
     * @param int $minItems MinItems
     * @return $this
     */
    public function setMinItems(int $minItems)
    {
        $this->minItems = $minItems;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUniqueItems(): bool
    {
        return $this->uniqueItems;
    }

    /**
     * @param bool $uniqueItems UniqueItems
     * @return $this
     */
    public function setUniqueItems(bool $uniqueItems)
    {
        $this->uniqueItems = $uniqueItems;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxProperties(): ?int
    {
        return $this->maxProperties;
    }

    /**
     * @param int $maxProperties MaxProperties
     * @return $this
     */
    public function setMaxProperties(int $maxProperties)
    {
        $this->maxProperties = $maxProperties;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMinProperties(): ?int
    {
        return $this->minProperties;
    }

    /**
     * @param int $minProperties MinProperties
     * @return $this
     */
    public function setMinProperties(int $minProperties)
    {
        $this->minProperties = $minProperties;

        return $this;
    }
}
