<?php

namespace SwaggerBake\Lib\OpenApi;

trait JsonSchemaTrait
{
    /** @var string */
    protected $title;

    /** @var mixed */
    protected $default;

    /** @var bool */
    protected $nullable = false;

    /** @var float|null */
    protected $multipleOf;

    /** @var float|null */
    protected $minimum;

    /** @var float|null */
    protected $maximum;

    /** @var bool */
    protected $exclusiveMinimum;

    /** @var bool */
    protected $exclusiveMaximum;

    /** @var int|null */
    protected $minLength;

    /** @var int|null */
    protected $maxLength;

    /** @var string */
    protected $pattern;

    /** @var int|null */
    protected $minItems;

    /** @var int|null */
    protected $maxItems;

    /** @var bool */
    protected $uniqueItems;

    /** @var int|null */
    protected $minProperties;

    /** @var int|null */
    protected $maxProperties;

    /**
     * @param $vars
     * @return array
     */
    public function removeEmptyVars($vars) : array
    {
        $empties = [
            'title','default','multipleOf','minimum','maximum','exclusiveMinimum','exclusiveMaximum','minLength',
            'maxLength','pattern','minItems','maxItems','uniqueItems','minProperties','maxProperties'
        ];

        foreach ($vars as $name => $value) {
            if (in_array($name, $empties) && (empty($value) || is_null($value) || $value == NULL)) {
                unset($vars[$name]);
            }
        }

        return $vars;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return JsonSchemaTrait
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     * @return JsonSchemaTrait
     */
    public function setDefault($default)
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
     * @param bool $nullable
     * @return JsonSchemaTrait
     */
    public function setNullable(bool $nullable): self
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
     * @param bool $deprecated
     * @return JsonSchemaTrait
     */
    public function setDeprecated(bool $deprecated): self
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
     * @param float $multipleOf
     * @return JsonSchemaTrait
     */
    public function setMultipleOf(float $multipleOf): self
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
     * @param float $maximum
     * @return JsonSchemaTrait
     */
    public function setMaximum(float $maximum): self
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
     * @param bool $exclusiveMaximum
     * @return JsonSchemaTrait
     */
    public function setExclusiveMaximum(bool $exclusiveMaximum): self
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
     * @param float $minimum
     * @return JsonSchemaTrait
     */
    public function setMinimum(float $minimum): self
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
     * @param bool $exclusiveMinimum
     * @return JsonSchemaTrait
     */
    public function setExclusiveMinimum(bool $exclusiveMinimum): self
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
     * @param int $maxLength
     * @return JsonSchemaTrait
     */
    public function setMaxLength(int $maxLength): self
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
     * @param int $minLength
     * @return JsonSchemaTrait
     */
    public function setMinLength(int $minLength): self
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
     * @param string $pattern
     * @return JsonSchemaTrait
     */
    public function setPattern(string $pattern): self
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
     * @param int $maxItems
     * @return JsonSchemaTrait
     */
    public function setMaxItems(int $maxItems): self
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
     * @param int $minItems
     * @return JsonSchemaTrait
     */
    public function setMinItems(int $minItems): self
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
     * @param bool $uniqueItems
     * @return JsonSchemaTrait
     */
    public function setUniqueItems(bool $uniqueItems): self
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
     * @param int $maxProperties
     * @return JsonSchemaTrait
     */
    public function setMaxProperties(int $maxProperties): self
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
     * @param int $minProperties
     * @return JsonSchemaTrait
     */
    public function setMinProperties(int $minProperties): self
    {
        $this->minProperties = $minProperties;
        return $this;
    }
}