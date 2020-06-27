<?php

namespace SwaggerBake\Lib\Operation;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use ReflectionProperty;
use SwaggerBake\Lib\Annotation\SwagDtoProperty;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Factory\ParameterFromAnnotationFactory;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Utility\DocBlockUtility;

class DtoParser
{
    /** @var string  */
    private $fqns;

    /** @var object  */
    private $instance;

    /** @var AnnotationReader  */
    private $annotationReader;

    public function __construct(string $fqns)
    {
        $this->fqns = $fqns;
        $this->instance = (new ReflectionClass($this->fqns))->newInstanceWithoutConstructor();
        $this->annotationReader = new AnnotationReader();
    }

    /**
     * Returns an array of Parameter instances for use in Query Parameters
     *
     * @return Parameter[]
     * @throws \ReflectionException
     */
    public function getParameters() : array
    {
        $parameters = [];

        $properties = $this->getClassProperties();

        $factory = new ParameterFromAnnotationFactory();

        foreach ($properties as $reflectionProperty) {

            $swagDtoProperty = $this->getSwagDtoProperty($reflectionProperty);
            if ($swagDtoProperty instanceof SwagDtoProperty) {
                $parameters[] = $factory->create($swagDtoProperty)->setIn('query');
                continue;
            }

            $docBlock = DocBlockUtility::getPropertyDocBlock($reflectionProperty);
            $vars = $docBlock->getTagsByName('var');
            if (!empty($vars)) {
                $var = reset($vars);
                $dataType = DocBlockUtility::getDocBlockConvertedVar($var);
            }

            $parameters[] = (new Parameter())
                ->setName($reflectionProperty->getName())
                ->setIn('query')
                ->setRequired(!empty($docBlock->getTagsByName('required')))
                ->setDescription($docBlock->getSummary())
                ->setSchema((new Schema())->setType($dataType ?? 'string'));
        }

        return $parameters;
    }

    /**
     * Returns an array of SchemaProperty instances for use in Body Requests
     *
     * @return SchemaProperty[]
     * @throws \ReflectionException
     */
    public function getSchemaProperties() : array
    {
        $schemaProperties = [];

        $properties = $this->getClassProperties();

        foreach ($properties as $name => $reflectionProperty) {

            $swagDtoProperty = $this->getSwagDtoProperty($reflectionProperty);
            if ($swagDtoProperty instanceof SwagDtoProperty) {
                $schemaProperties[] = (new SchemaProperty())
                    ->setDescription($swagDtoProperty->description ?? '')
                    ->setName($swagDtoProperty->name)
                    ->setType($swagDtoProperty->type ?? 'string')
                    ->setRequired($swagDtoProperty->required)
                    ->setEnum($swagDtoProperty->enum)
                    ->setDeprecated($swagDtoProperty->deprecated)
                    ->setFormat($swagDtoProperty->format ?? '')
                    ->setExample($swagDtoProperty->example ?? '')
                ;
                continue;
            }

            $docBlock = DocBlockUtility::getPropertyDocBlock($reflectionProperty);
            $vars = $docBlock->getTagsByName('var');
            if (empty($vars)) {
                throw new SwaggerBakeRunTimeException('@var must be set for ' . $class . '::' . $name);
            }
            $var = reset($vars);
            $dataType = DocBlockUtility::getDocBlockConvertedVar($var);

            $schemaProperties[] = (new SchemaProperty())
                ->setDescription($docBlock->getSummary())
                ->setName($name)
                ->setType($dataType)
                ->setRequired(!empty($docBlock->getTagsByName('required')));
        }

        return $schemaProperties;
    }

    /**
     * Gets an instance of SwagDtoProperty, null otherwise
     *
     * @param ReflectionProperty $reflectionProperty
     * @return Parameter|null
     */
    private function getSwagDtoProperty(ReflectionProperty $reflectionProperty) : ?SwagDtoProperty
    {
        try {
            $annotation = $this->annotationReader->getPropertyAnnotation($reflectionProperty, SwagDtoProperty::class);
            if ($annotation instanceof SwagDtoProperty && !empty($annotation->name)) {
                return $annotation;
            }
        } catch (AnnotationException $e) {

        }

        return null;
    }

    /**
     * Returns an array of class properties
     *
     * @return array
     */
    private function getClassProperties() : array
    {
        $properties = DocBlockUtility::getProperties($this->instance);

        if (empty($properties)) {
            return [];
        }

        return array_filter($properties, function ($property) {
            if (!isset($property->class) || $property->class != get_class($this->instance)) {
                return null;
            }
            return true;
        });
    }
}