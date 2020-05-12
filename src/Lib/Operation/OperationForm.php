<?php

namespace SwaggerBake\Lib\Operation;

use LogicException;
use ReflectionClass;
use SwaggerBake\Lib\Annotation\SwagForm;
use SwaggerBake\Lib\Annotation\SwagDto;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\RequestBody;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Utility\DocBlockUtility;

class OperationForm
{
    /**
     * @param Operation $operation
     * @param array $annotations
     * @return Operation
     * @throws \ReflectionException
     */
    public function getOperationWithFormProperties(Operation $operation, array $annotations) : Operation
    {
        if ($operation->getHttpMethod() != 'POST') {
            return $operation;
        }

        $operation = $this->withSwagForm($operation, $annotations);
        $operation = $this->withSwagDto($operation, $annotations);

        return $operation;
    }

    /**
     * @param Operation $operation
     * @param array $annotations
     * @return Operation
     */
    private function withSwagForm(Operation $operation, array $annotations) : Operation
    {
        $swagForms = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagForm;
        });

        $schema = new Schema();

        foreach ($swagForms as $annotation) {
            $schema->pushProperty(
                (new SchemaProperty())
                    ->setDescription($annotation->description)
                    ->setName($annotation->name)
                    ->setType($annotation->type)
                    ->setRequired($annotation->required)
            );
        }

        $requestBody = $operation->getRequestBody() ?? new RequestBody();

        $requestBody->pushContent(
            (new Content())->setMimeType('application/x-www-form-urlencoded')->setSchema($schema)
        );

        return $operation->setRequestBody($requestBody);
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

        if (empty($filteredProperties)) {
            return $operation;
        }

        $requestBody = new RequestBody();
        $schema = (new Schema())->setType('object');

        foreach ($filteredProperties as $name => $reflectionProperty) {
            $docBlock = DocBlockUtility::getPropertyDocBlock($reflectionProperty);
            $vars = $docBlock->getTagsByName('var');
            if (empty($vars)) {
                throw new LogicException('@var must be set for ' . $class . '::' . $name);
            }
            $var = reset($vars);
            $dataType = DocBlockUtility::getDocBlockConvertedVar($var);

            $schema->pushProperty(
                (new SchemaProperty())
                    ->setDescription($docBlock->getSummary())
                    ->setName($name)
                    ->setType($dataType)
                    ->setRequired(!empty($docBlock->getTagsByName('required')))
            );
        }

        $operation->setRequestBody(
            $requestBody->pushContent(
                (new Content())
                    ->setMimeType('application/x-www-form-urlencoded')
                    ->setSchema($schema)
            )
        );

        return $operation;
    }
}