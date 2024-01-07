<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use ReflectionMethod;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Attribute\OpenApiPathParam;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Route\RouteDecorator;

/**
 * Class OperationPath
 *
 * @package SwaggerBake\Lib\Operation
 */
class OperationPathParameter
{
    /**
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation instance of Operation
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route instance of RouteDecorator
     * @param \ReflectionMethod|null $reflectionMethod ReflectionMethod or null
     * @param \SwaggerBake\Lib\OpenApi\Schema|null $schema instance of Schema or null
     */
    public function __construct(
        private Operation $operation,
        private RouteDecorator $route,
        private ?ReflectionMethod $reflectionMethod = null,
        private ?Schema $schema = null
    ) {
    }

    /**
     * Adds Path Parameters to the Operation
     *
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    public function getOperationWithPathParameters(): Operation
    {
        $this->assignPathParametersFromRoute();
        $this->updatePathParametersUsingAnnotations();

        return $this->operation;
    }

    /**
     * Adds Path Parameters from existing routes
     *
     * @return void
     */
    private function assignPathParametersFromRoute(): void
    {
        $pieces = explode('/', $this->route->getTemplate());
        $results = array_filter($pieces, function ($piece) {
            return str_starts_with($piece, ':') || str_starts_with($piece, '{');
        });

        $properties = $this->schema instanceof Schema ? $this->schema->getProperties() : [];

        foreach ($results as $result) {
            $id = str_replace([':','{','}'], ['','',''], strtolower($result));

            if (isset($properties[$id])) {
                $type = $properties[$id]->getType();
                $format = $properties[$id]->getFormat();
                $description = $properties[$id]->getDescription();
            }

            $this->operation->pushParameter(
                new Parameter(
                    in: 'path',
                    name: $id,
                    description: $description ?? null,
                    required: true,
                    schema: (new Schema())->setType($type ?? 'string')->setFormat($format ?? '')
                )
            );
        }
    }

    /**
     * Updates Path Parameters using values from OpenApiPathParam attributes. The path parameter must already exist
     * having been added from routes. This will not add new parameters, only update existing ones.
     *
     * @return void
     */
    private function updatePathParametersUsingAnnotations(): void
    {
        if (!$this->reflectionMethod instanceof ReflectionMethod) {
            return;
        }

        /** @var array<\SwaggerBake\Lib\Attribute\OpenApiPathParam> $openApiPathParams */
        $openApiPathParams = (new AttributeFactory(
            $this->reflectionMethod,
            OpenApiPathParam::class
        ))->createMany();

        $parameters = $this->operation->getParameters();

        foreach ($openApiPathParams as $pathParameter) {
            $params = array_filter($this->operation->getParameters(), function ($parameter) use ($pathParameter) {
                return $parameter->getIn() == 'path' && $pathParameter->name == $parameter->getName();
            });

            $keys = array_keys($params);
            $index = reset($keys);

            $parameters[$index] = $pathParameter->createParameter();
        }

        $this->operation->setParameters($parameters);
    }
}
