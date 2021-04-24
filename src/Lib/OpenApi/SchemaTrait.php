<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\OpenApi;

trait SchemaTrait
{
    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $type = '';

    /**
     * @var string
     */
    private $format;

    /**
     * @var array
     */
    private $enum = [];

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
     * @return string|null
     */
    public function getFormat(): ?string
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
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param ?string $description Description
     * @return $this
     */
    public function setDescription(?string $description)
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
     * @param array $enum Enumerated list
     * @return $this
     */
    public function setEnum(array $enum)
    {
        $this->enum = $enum;

        return $this;
    }
}
