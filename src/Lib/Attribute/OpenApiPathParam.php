<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;

/**
 * Attribute for describing Path Parameter Objects.
 *
 * The name specified for Path Parameter must exist in your existing routes for the path parameter to be applied.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class OpenApiPathParam extends AbstractOpenApiParameter
{
    /**
     * @param string $name Name of the property, required if $ref is empty [default: ""]
     * @param string $ref OpenAPI $ref, required if $name is empty [default: ""]
     * @param string $type The scalar data type (e.g. string, integer) [default: "string"]
     * @param string $format The data format (e.g. date-time, uuid) [default: ""]
     * @param string $description A description. [default: ""]
     * @param string $example An example. [default: ""]
     * @param bool $allowReserved Allow reserved keywords? [default: false]
     * @param bool $isRequired Is the path parameter required? [default: false]
     */
    public function __construct(
        string $name = '',
        string $ref = '',
        string $type = 'string',
        string $format = '',
        string $description = '',
        string $example = '',
        bool $allowReserved = false,
        bool $isRequired = false,
    ) {
        parent::__construct(
            name: $name,
            ref: $ref,
            type: $type,
            format: $format,
            description: $description,
            example: $example,
            isRequired: $isRequired,
            allowReserved: $allowReserved
        );
    }

    /**
     * Create an OpenApi Parameter
     *
     * @return \SwaggerBake\Lib\OpenApi\Parameter
     */
    public function createParameter(): Parameter
    {
        return (new Parameter(in: 'path', name: $this->name))
            ->setExample($this->example)
            ->setDescription($this->description)
            ->setAllowReserved($this->allowReserved)
            ->setRequired($this->isRequired)
            ->setSchema(
                (new Schema())
                    ->setType($this->type)
                    ->setFormat($this->format)
            );
    }
}
