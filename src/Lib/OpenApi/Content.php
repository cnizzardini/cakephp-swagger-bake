<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

/**
 * Class Content
 *
 * @package SwaggerBake\Lib\OpenApi
 * @see https://swagger.io/docs/specification/describing-request-body/
 */
class Content implements JsonSerializable
{
    /**
     * @param string $mimeType A mimetype such as "application/json"
     * @param \SwaggerBake\Lib\OpenApi\Schema|string $schema An instance of the schema or an OpenApi $ref string
     * @todo add enum for $mimeType argument in PHP 8.1
     */
    public function __construct(
        private string $mimeType,
        private Schema|string $schema
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $vars = get_object_vars($this);
        unset($vars['mimeType']);
        unset($vars['schema']);

        if ($this->schema instanceof Schema) {
            if ($this->schema->getRefPath()) {
                $vars['schema']['required'] = array_values($this->schema->getRequired());
                $vars['schema']['allOf'][] = [
                    '$ref' => $this->schema->getRefPath(),
                ];
                if (empty($vars['schema']['required'])) {
                    unset($vars['schema']['required']);
                }
            } else {
                $vars['schema'] = $this->schema;
            }
        } elseif (is_string($this->schema)) {
            $vars['schema']['$ref'] = $this->schema;
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
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType Mime type e.g. application/json, application/xml, etc...
     * @return $this
     */
    public function setMimeType(string $mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @return \SwaggerBake\Lib\OpenApi\Schema|string
     */
    public function getSchema(): Schema|string
    {
        return $this->schema;
    }

    /**
     * Can be either a schema $ref string such as '#/components/schemas/Pet' or a Schema instance.
     *
     * @param \SwaggerBake\Lib\OpenApi\Schema|string $schema Schema
     * @return $this
     */
    public function setSchema(Schema|string $schema)
    {
        $this->schema = $schema;

        return $this;
    }
}
