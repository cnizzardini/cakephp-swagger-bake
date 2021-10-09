<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use ReflectionClass;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Attribute\OpenApiDtoQuery;
use SwaggerBake\Lib\Attribute\OpenApiDtoRequestBody;

class DtoParser
{
    private ReflectionClass $reflection;

    /**
     * @param string $fqn Fully qualified namespace of the DTO
     * @throws \ReflectionException
     */
    public function __construct(string $fqn)
    {
        $this->reflection = new ReflectionClass($fqn);
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
     * Returns an array of SchemaProperty instances for use in Body Requests
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
