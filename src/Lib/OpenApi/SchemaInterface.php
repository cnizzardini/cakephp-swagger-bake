<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\OpenApi;

interface SchemaInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name Name
     * @return $this
     */
    public function setName(string $name);

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type Type
     * @return $this
     */
    public function setType(string $type);

    /**
     * @return string|null
     */
    public function getFormat(): ?string;

    /**
     * @param string $format Format
     * @return $this
     */
    public function setFormat(string $format);

    /**
     * @return array
     */
    public function getEnum(): array;

    /**
     * @param array $enum Enumerated list
     * @return $this
     */
    public function setEnum(array $enum);
}
