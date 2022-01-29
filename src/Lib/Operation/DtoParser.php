<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use ReflectionClass;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Attribute\OpenApiDtoQuery;
use SwaggerBake\Lib\Attribute\OpenApiDtoRequestBody;

/**
 * Parses the ReflectionClass (or FQN into ReflectionClass) and builds a Schema instance with instances of
 * SchemaProperty. This is used by DTO attributes and Response attributes.
 */
class DtoParser
{
    /**
     * @param ReflectionClass|string $reflection ReflectionClass instance or the fully qualified namespace of the DTO
     *      to be converted into a ReflectionClass instance.
     * @throws \ReflectionException
     */
    public function __construct(private ReflectionClass|string $reflection)
    {
        $this->reflection = is_string($this->reflection) ? new ReflectionClass($reflection) : $reflection;
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
            $openApiDtoQuery = (new AttributeFactory(
                $reflectionProperty,
                OpenApiDtoQuery::class
            ))->createOneOrNull();

            if (!$openApiDtoQuery instanceof OpenApiDtoQuery) {
                continue;
            }

            $parameters[] = $openApiDtoQuery->create();
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
            $openApiDtoRequestBody = (new AttributeFactory(
                $reflectionProperty,
                OpenApiDtoRequestBody::class
            ))->createOneOrNull();

            if (!$openApiDtoRequestBody instanceof OpenApiDtoRequestBody) {
                continue;
            }

            $schemaProperties[] = $openApiDtoRequestBody->create();
        }

        return $schemaProperties ?? [];
    }
}
