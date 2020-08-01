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
     * @var string
     */
    private $description = '';

    /**
     * @var string
     */
    private $url = '';

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
     * @param string $description Describes the external documentation
     * @return $this
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url The Url (hyperlink) to the external documentation
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }
}
