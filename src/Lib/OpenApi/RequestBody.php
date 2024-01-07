<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

/**
 * Class RequestBody
 *
 * @package SwaggerBake\Lib\OpenApi
 * @see https://swagger.io/docs/specification/describing-request-body/
 */
class RequestBody implements JsonSerializable
{
    /**
     * @param string|null $description An optional description
     * @param array<\SwaggerBake\Lib\OpenApi\Content> $content Array of OpenApi Content
     * @param bool $required Is this required?
     * @param bool $ignoreCakeSchema Should cakephp schema be ignored?
     */
    public function __construct(
        private ?string $description = null,
        private array $content = [],
        private bool $required = false,
        private bool $ignoreCakeSchema = false
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $vars = get_object_vars($this);
        unset($vars['ignoreCakeSchema']);
        if ($this->required == false) {
            unset($vars['required']);
        }
        if (empty($this->description)) {
            unset($vars['description']);
        }

        return $vars;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
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
     * @return array|array<\SwaggerBake\Lib\OpenApi\Content>
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @param \SwaggerBake\Lib\OpenApi\Content $content Content
     * @return $this
     */
    public function pushContent(Content $content)
    {
        $this->content[$content->getMimeType()] = $content;

        return $this;
    }

    /**
     * @param string $mimeType Mime type i.e. application/json, application/xml
     * @return \SwaggerBake\Lib\OpenApi\Content|null
     */
    public function getContentByType(string $mimeType): ?Content
    {
        if (isset($this->content[$mimeType])) {
            return $this->content[$mimeType];
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required Required
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
    public function isIgnoreCakeSchema(): bool
    {
        return $this->ignoreCakeSchema;
    }

    /**
     * @param bool $ignoreCakeSchema Ignore cake schema
     * @return $this
     */
    public function setIgnoreCakeSchema(bool $ignoreCakeSchema)
    {
        $this->ignoreCakeSchema = $ignoreCakeSchema;

        return $this;
    }
}
