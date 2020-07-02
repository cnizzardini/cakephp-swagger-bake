<?php

namespace SwaggerBake\Lib\Schema;

use Cake\Utility\Inflector;
use Cake\Validation\Validator;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use SwaggerBake\Lib\Annotation\SwagEntity;
use SwaggerBake\Lib\Annotation\SwagEntityAttribute;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Decorator\PropertyDecorator;
use SwaggerBake\Lib\Decorator\EntityDecorator;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Utility\AnnotationUtility;
use SwaggerBake\Lib\Utility\DataTypeConversion;

/**
 * Class SchemaFactory
 * @package SwaggerBake\Lib\Factory
 *
 * Creates an instance of SwaggerBake\Lib\OpenApi\Schema per OpenAPI specifications
 */
class SchemaFactory
{
    /** @var Validator */
    private $validator;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * @param EntityDecorator $entity
     * @return Schema|null
     */
    public function create(EntityDecorator $entity) : ?Schema
    {
        $swagEntity = $this->getSwagEntityAnnotation($entity);

        if ($swagEntity !== null && $swagEntity->isVisible === false) {
            return null;
        }

        $this->validator = $this->getValidator($entity->getName());

        $docBlock = $this->getDocBlock($entity);

        $properties = $this->getProperties($entity);

        $schema = (new Schema())
            ->setName($entity->getName())
            ->setTitle($swagEntity !== null ? $swagEntity->description : null)
            ->setType('object')
            ->setProperties($properties)
        ;

        if ($swagEntity !== null && isset($swagEntity->description)) {
            $schema->setDescription($swagEntity->description);
        } else {
            $schema->setDescription($docBlock ? $docBlock->getSummary() : null);
        }

        $requiredProperties = array_filter($properties, function ($property) {
            return $property->isRequired();
        });

        if (!empty($requiredProperties)) {
            $schema->setRequired(array_keys($requiredProperties));
        }

        return $schema;
    }

    /**
     * @param EntityDecorator $entity
     * @return array
     */
    private function getProperties(EntityDecorator $entity) : array
    {
        $return = [];
        $factory = new SchemaPropertyFactory($this->validator);

        foreach ($entity->getProperties() as $attribute) {
            $return[$attribute->getName()] = $factory->create($attribute);
        }

        $return = array_merge($return, $this->getSwagPropertyAnnotations($entity));

        return $return;
    }

    /**
     * @param EntityDecorator $entity
     * @return DocBlock|null
     */
    private function getDocBlock(EntityDecorator $entity) : ?DocBlock
    {
        try {
            $instance = $entity->getEntity();
            $reflectionClass = new ReflectionClass(get_class($instance));
        } catch (\Exception $e) {
            return null;
        }

        $comments = $reflectionClass->getDocComment();

        if (!$comments) {
            return null;
        }

        $docFactory = DocBlockFactory::createInstance();
        return $docFactory->create($comments);
    }

    /**
     * @param string $className
     * @return string|null
     */
    private function getTableFromNamespaces(string $className) : ?string
    {
        $namespaces = $this->config->getNamespaces();

        if (!isset($namespaces['tables']) || !is_array($namespaces['tables'])) {
            throw new SwaggerBakeRunTimeException(
                'Invalid configuration, missing SwaggerBake.namespaces.tables'
            );
        }

        foreach ($namespaces['tables'] as $namespace) {
            $table = $namespace . 'Model\Table\\' . $className;
            if (class_exists($table, true)) {
                return $table;
            }
        }

        return null;
    }

    /**
     * Returns key-value pair of property name => SchemaProperty
     *
     * @param EntityDecorator $entity
     * @return SchemaProperty[]
     */
    private function getSwagPropertyAnnotations(EntityDecorator $entity) : array
    {
        $return = [];

        $annotations = AnnotationUtility::getClassAnnotationsFromInstance($entity->getEntity());

        $swagEntityAttributes = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagEntityAttribute;
        });

        $factory = new SchemaPropertyFromAnnotationFactory();

        foreach ($swagEntityAttributes as $swagEntityAttribute) {
            $return[$swagEntityAttribute->name] = $factory->create($swagEntityAttribute);
        }

        return $return;
    }

    /**
     * Returns instance of SwagEntity annotation, otherwise null
     *
     * @param EntityDecorator $entity
     * @return SwagEntity|null
     */
    private function getSwagEntityAnnotation(EntityDecorator $entity) : ?SwagEntity
    {
        $annotations = AnnotationUtility::getClassAnnotationsFromInstance($entity->getEntity());

        foreach ($annotations as $annotation) {
            if ($annotation instanceof SwagEntity) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * @param string $className
     * @return Validator|null
     */
    private function getValidator(string $className) : ?Validator
    {
        try {
            $table = $this->getTableFromNamespaces(Inflector::pluralize($className) . 'Table');
            $instance = new $table;
            $validator = $instance->validationDefault(new Validator());
        } catch (\Exception $e) {
            return new Validator();
        }

        return $validator;
    }
}