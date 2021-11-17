<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

/**
 * Class OperationExternalDoc
 *
 * @package SwaggerBake\Lib\OpenApi
 * @see https://swagger.io/docs/specification/paths-and-operations/
 */
class OperationExternalDoc implements JsonSerializable
{
    /**
     * @param string $url URL to the documentation
     * @param string $description Description of the link
     */
    public function __construct(
        private string $url,
        private string $description = ''
    ) {
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
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}
