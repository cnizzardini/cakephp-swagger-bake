<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Utility\OpenApiDataType;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class OpenApiHeader
{
    /**
     * @param string $name The name of the parameter. Parameter names are case-sensitive.
     * @param string $ref An OpenAPI $ref to your OpenAPI YAML.
     * @param string $format The type of data accepted, typically string.
     * @param string $type The expected format of the type, for instance date-time.
     * @param string $description A brief description of the parameter. This could contain examples of use.
     * @param bool $isRequired Determines whether this parameter is mandatory.
     * @param array $enum A list of enumerated values allowed for the header
     * @param bool $isDeprecated Specifies that a parameter is deprecated and SHOULD be transitioned out of usage.
     * Default value is false.
     * @param bool $explode When this is true, parameter values of type array or object generate separate parameters
     * for each value of the array or key-value pair of the map.
     * @param string $style Describes how the parameter value will be serialized depending on the type of the parameter
     * value.
     * @param string|int|bool $example Example of the parameter’s potential value. The example SHOULD match the specified schema
     * and encoding properties if present.
     * @param bool $allowEmptyValue Are empty values allowed?
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public readonly string $name = '',
        public readonly string $ref = '',
        public readonly string $format = '',
        public readonly string $type = 'string',
        public readonly string $description = '',
        public readonly bool $isRequired = false,
        public readonly array $enum = [],
        public readonly bool $isDeprecated = false,
        public readonly bool $explode = false,
        public readonly string $style = '',
        public readonly string|bool|int $example = '',
        public readonly bool $allowEmptyValue = false
    ) {
        if (empty($ref) && empty($name)) {
            throw new SwaggerBakeRunTimeException('One of ref or name must be defined');
        }

        if (!in_array($type, OpenApiDataType::TYPES)) {
            throw new SwaggerBakeRunTimeException(
                sprintf(
                    'Invalid Data Type, given %s for %s but must be one of: %s',
                    $type,
                    $name,
                    implode(',', OpenApiDataType::TYPES)
                )
            );
        }
    }

    /**
     * Used internally to create a Parameter
     *
     * @internal
     * @return \SwaggerBake\Lib\OpenApi\Parameter
     */
    public function createParameter(): Parameter
    {
        return (new Parameter('header', $this->ref, $this->name))
            ->setDescription($this->description)
            ->setRequired($this->isRequired)
            ->setDeprecated($this->isDeprecated)
            ->setStyle($this->style)
            ->setExplode($this->explode)
            ->setExample($this->example)
            ->setAllowEmptyValue($this->allowEmptyValue)
            ->setSchema(
                (new Schema())
                    ->setType($this->type)
                    ->setEnum($this->enum)
                    ->setFormat($this->format)
            );
    }
}
