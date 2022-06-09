<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use ReflectionClass;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Attribute\OpenApiDtoQuery;
use SwaggerBake\Lib\Attribute\OpenApiDtoRequestBody;
use SwaggerBake\Lib\Attribute\OpenApiQueryParam;
use SwaggerBake\Lib\Attribute\OpenApiSchemaProperty;

/**
 * Parses the ReflectionClass (or FQN into ReflectionClass) and builds a Schema instance with instances of
 * SchemaProperty. This is used by DTO attributes and Response attributes.
 */
class DtoParser
{
    private ReflectionClass $reflection;

    /**
     * @param \ReflectionClass|string $reflection ReflectionClass instance or the fully qualified namespace of the DTO
     * to be converted into a ReflectionClass instance.
     */
    public function __construct(ReflectionClass|string $reflection)
    {
        if (is_string($reflection) && class_exists($reflection)) {
            $this->reflection = new ReflectionClass($reflection);
        } elseif ($reflection instanceof ReflectionClass) {
            $this->reflection = $reflection;
        }
    }

    /**
     * Returns an array of Parameter instances for use in Query Parameters
     *
     * @return \SwaggerBake\Lib\OpenApi\Parameter[]
     * @throws \ReflectionException
     */
    public function getParameters(): array
    {
        foreach ($this->reflection->getProperties() as $reflectionProperty) {
            $queryParam = (new AttributeFactory(
                $reflectionProperty,
                OpenApiQueryParam::class
            ))->createOneOrNull();

            if ($queryParam instanceof OpenApiQueryParam) {
                $parameters[] = $queryParam->createParameter();
            }
        }

        return $parameters ?? [];
    }

    /**
     * Returns an array of SchemaProperty instances for use in Body Requests or Responses.
     *
     * @return \SwaggerBake\Lib\OpenApi\SchemaProperty[]
     * @throws \ReflectionException
     */
    public function getSchemaProperties(): array
    {
        foreach ($this->reflection->getProperties() as $reflectionProperty) {
            $schemaProperty = (new AttributeFactory(
                $reflectionProperty,
                OpenApiSchemaProperty::class
            ))->createOneOrNull();

            if ($schemaProperty instanceof OpenApiSchemaProperty) {
                $schemaProperties[] = $schemaProperty->create();
            }
        }

        return $schemaProperties ?? [];
    }
}
