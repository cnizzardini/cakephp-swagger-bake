<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Schema;

use Cake\Utility\Inflector;
use Cake\Validation\Validator;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use SwaggerBake\Lib\Annotation\SwagEntity;
use SwaggerBake\Lib\Annotation\SwagEntityAttribute;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Decorator\EntityDecorator;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Utility\AnnotationUtility;

/**
 * Class SchemaFactory
 *
 * @package SwaggerBake\Lib\Factory
 *
 * Creates an instance of SwaggerBake\Lib\OpenApi\Schema per OpenAPI specifications
 */
class SchemaFactory
{
    /**
     * @var \Cake\Validation\Validator
     */
    private $validator;

    /**
     * @var \SwaggerBake\Lib\Configuration
     */
    private $config;

    /**
     * @var string
     */
    public const WRITEABLE_PROPERTIES = 2;

    /**
     * @var string
     */
    public const READABLE_PROPERTIES = 4;

    /**
     * @var string
     */
    public const ALL_PROPERTIES = 6;

    /**
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     */
    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * Creates an instance of Schema for an EntityDecorator, returns null if the Entity is set to invisible
     *
     * @param \SwaggerBake\Lib\Decorator\EntityDecorator $entity EntityDecorator
     * @param int $propertyType see public constants for options
     * @return \SwaggerBake\Lib\OpenApi\Schema|null
     */
    public function create(EntityDecorator $entity, int $propertyType = 6): ?Schema
    {
        $swagEntity = $this->getSwagEntityAnnotation($entity);

        if ($swagEntity !== null && $swagEntity->isVisible === false) {
            return null;
        }

        $this->validator = $this->getValidator($entity->getName());

        $docBlock = $this->getDocBlock($entity);

        $properties = $this->getProperties($entity, $propertyType);

        $schema = (new Schema())
            ->setName($entity->getName())
            ->setTitle($swagEntity !== null ? $swagEntity->description : null)
            ->setType('object')
            ->setProperties($properties);

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
     * @param \SwaggerBake\Lib\Decorator\EntityDecorator $entity EntityDecorator
     * @param int $propertyType see public constants for options
     * @return array
     */
    private function getProperties(EntityDecorator $entity, int $propertyType): array
    {
        $return = [];
        $factory = new SchemaPropertyFactory($this->validator);

        foreach ($entity->getProperties() as $attribute) {
            $return[$attribute->getName()] = $factory->create($attribute);
        }

        $return = array_merge($return, $this->getSwagPropertyAnnotations($entity));

        if ($propertyType === self::ALL_PROPERTIES) {
            return $return;
        }

        return array_filter($return, function (SchemaProperty $property) use ($propertyType) {
            if ($propertyType == self::READABLE_PROPERTIES && !$property->isWriteOnly()) {
                return true;
            }
            if ($propertyType == self::WRITEABLE_PROPERTIES && !$property->isReadOnly()) {
                return true;
            }
        });
    }

    /**
     * @param \SwaggerBake\Lib\Decorator\EntityDecorator $entity EntityDecorator
     * @return \phpDocumentor\Reflection\DocBlock|null
     */
    private function getDocBlock(EntityDecorator $entity): ?DocBlock
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
     * @param string $className Name of the Table class
     * @return string|null
     */
    private function getTableFromNamespaces(string $className): ?string
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
     * @param \SwaggerBake\Lib\Decorator\EntityDecorator $entity EntityDecorator
     * @return \SwaggerBake\Lib\OpenApi\SchemaProperty[]
     */
    private function getSwagPropertyAnnotations(EntityDecorator $entity): array
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
     * @param \SwaggerBake\Lib\Decorator\EntityDecorator $entity EntityDecorator
     * @return \SwaggerBake\Lib\Annotation\SwagEntity|null
     */
    private function getSwagEntityAnnotation(EntityDecorator $entity): ?SwagEntity
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
     * Gets the Table classes Validator
     *
     * @param string $className Name of the Table class
     * @return \Cake\Validation\Validator|null
     */
    private function getValidator(string $className): ?Validator
    {
        try {
            $table = $this->getTableFromNamespaces(Inflector::pluralize($className) . 'Table');
            $instance = new $table();
            $validator = $instance->validationDefault(new Validator());
        } catch (\Exception $e) {
            return new Validator();
        }

        return $validator;
    }
}
