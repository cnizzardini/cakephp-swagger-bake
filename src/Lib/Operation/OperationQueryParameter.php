<?php

namespace SwaggerBake\Lib\Operation;

use LogicException;
use ReflectionClass;
use SwaggerBake\Lib\Annotation\SwagDto;
use SwaggerBake\Lib\Annotation\SwagPaginator;
use SwaggerBake\Lib\Annotation\SwagQuery;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Utility\DocBlockUtility;

class OperationQueryParameter
{
    /**
     * @param Operation $operation
     * @param array $annotations
     * @return Operation
     */
    public function getOperationWithQueryParameters(Operation $operation, array $annotations) : Operation
    {
        if ($operation->getHttpMethod() != 'GET') {
            return $operation;
        }

        $operation = $this->withSwagPaginator($operation, $annotations);
        $operation = $this->withSwagQuery($operation, $annotations);
        try {
            $operation = $this->withSwagDto($operation, $annotations);
        } catch (\ReflectionException $e) {
            throw new SwaggerBakeRunTimeException('ReflectionException: ' . $e->getMessage());
        }

        return $operation;
    }

    /**
     * @param Operation $operation
     * @param array $annotations
     * @return Operation
     */
    private function withSwagPaginator(Operation $operation, array $annotations) : Operation
    {
        $swagPaginator = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagPaginator;
        });

        if (empty($swagPaginator)) {
            return $operation;
        }

        $parameter = (new Parameter())
            ->setAllowEmptyValue(false)
            ->setDeprecated(false)
            ->setRequired(false)
            ->setIn('query');

        $params = ['page' => 'integer', 'limit' => 'integer', 'sort' => 'string', 'direction' => 'string'];
        foreach ($params as $name => $type) {
            $operation->pushParameter(
                (clone $parameter)->setName($name)->setSchema((new Schema())->setType($type))
            );
        }

        return $operation;
    }

    /**
     * @param Operation $operation
     * @param array $annotations
     * @return Operation
     */
    private function withSwagQuery(Operation $operation, array $annotations) : Operation
    {
        $swagQueries = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagQuery;
        });

        foreach ($swagQueries as $annotation) {
            $parameter = (new Parameter())
                ->setName($annotation->name)
                ->setDescription($annotation->description)
                ->setAllowEmptyValue(false)
                ->setDeprecated(false)
                ->setRequired($annotation->required)
                ->setIn('query')
                ->setSchema((new Schema())->setType($annotation->type))
            ;

            $operation->pushParameter($parameter);
        }

        return $operation;
    }

    /**
     * @param Operation $operation
     * @param array $annotations
     * @return Operation
     * @throws \ReflectionException
     */
    private function withSwagDto(Operation $operation, array $annotations) : Operation
    {
        $swagDtos = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagDto;
        });

        if (empty($swagDtos)) {
            return $operation;
        }

        $dto = reset($swagDtos);
        $class = $dto->class;

        if (!class_exists($class)) {
            return $operation;
        }

        $instance = (new ReflectionClass($class))->newInstanceWithoutConstructor();
        $properties = DocBlockUtility::getProperties($instance);

        if (empty($properties)) {
            return $operation;
        }

        $filteredProperties = array_filter($properties, function ($property) use ($instance) {
            if (!isset($property->class) || $property->class != get_class($instance)) {
                return null;
            }
            return true;
        });

        foreach ($filteredProperties as $name => $reflectionProperty) {
            $docBlock = DocBlockUtility::getPropertyDocBlock($reflectionProperty);
            $vars = $docBlock->getTagsByName('var');
            if (empty($vars)) {
                throw new LogicException('@var must be set for ' . $class . '::' . $name);
            }
            $var = reset($vars);
            $dataType = DocBlockUtility::getDocBlockConvertedVar($var);

            $operation->pushParameter(
                (new Parameter())
                    ->setName($name)
                    ->setIn('query')
                    ->setRequired(!empty($docBlock->getTagsByName('required')))
                    ->setDescription($docBlock->getSummary())
                    ->setSchema((new Schema())->setType($dataType))
            );
        }

        return $operation;
    }
}