<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use SwaggerBake\Lib\Annotation\SwagPathParameter;
use SwaggerBake\Lib\Decorator\RouteDecorator;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;

/**
 * Class OperationPath
 *
 * @package SwaggerBake\Lib\Operation
 */
class OperationPath
{
    /**
     * @var \SwaggerBake\Lib\OpenApi\Operation
     */
    private $operation;

    /**
     * @var \SwaggerBake\Lib\Decorator\RouteDecorator
     */
    private $route;

    /**
     * @var array
     */
    private $annotations;

    /**
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param \SwaggerBake\Lib\Decorator\RouteDecorator $route RouteDecorator
     * @param array $annotations Array of annotation objects
     */
    public function __construct(Operation $operation, RouteDecorator $route, array $annotations)
    {
        $this->operation = $operation;
        $this->route = $route;
        $this->annotations = $annotations;
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
            return substr($piece, 0, 1) == ':' ? true : null;
        });

        foreach ($results as $result) {
            $name = strtolower($result);

            if (substr($name, 0, 1) == ':') {
                $name = substr($name, 1);
            }

            $this->operation->pushParameter(
                (new Parameter())
                    ->setName($name)
                    ->setAllowEmptyValue(false)
                    ->setDeprecated(false)
                    ->setRequired(true)
                    ->setIn('path')
                    ->setSchema((new Schema())->setType('string'))
            );
        }
    }

    /**
     * Updates Path Parameters using values from SwagPathParameter annotation. The path parameter must already exist
     * having been adding from routes. This will not add new parameters, only update existing ones.
     *
     * @return void
     */
    private function updatePathParametersUsingAnnotations(): void
    {
        /**
         * @var \SwaggerBake\Lib\Annotation\SwagPathParameter[] $swagPathParameters
         */
        $swagPathParameters = array_filter($this->annotations, function ($annotation) {
            return $annotation instanceof SwagPathParameter;
        });

        if (empty($swagPathParameters)) {
            return;
        }

        $parameters = $this->operation->getParameters();

        foreach ($swagPathParameters as $pathParameter) {
            $params = array_filter($this->operation->getParameters(), function ($parameter) use ($pathParameter) {
                return $parameter->getIn() == 'path' && $pathParameter->name == $parameter->getName();
            });

            $keys = array_keys($params);
            $index = reset($keys);

            $parameters[$index]
                ->setName($pathParameter->name)
                ->setExample($pathParameter->example)
                ->setAllowReserved($pathParameter->allowReserved)
                ->setSchema(
                    (new Schema())
                        ->setType($pathParameter->type)
                        ->setFormat($pathParameter->format)
                        ->setDescription($pathParameter->description)
                );
        }

        $this->operation->setParameters($parameters);
    }
}
