<?php

namespace SwaggerBake\Lib\Factory;

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
    /** @var string[]  */
    private const READ_ONLY_FIELDS = ['created','modified'];

    /** @var string[]  */
    private const DATETIME_TYPES = ['date','datetime','timestamp'];

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
        if (!$this->isSwaggable($entity)) {
            return null;
        }

        $this->validator = $this->getValidator($entity->getName());

        $docBlock = $this->getDocBlock($entity);

        $properties = $this->getProperties($entity);

        $schema = new Schema();
        $schema
            ->setName($entity->getName())
            ->setDescription($docBlock ? $docBlock->getSummary() : '')
            ->setType('object')
            ->setProperties($properties)
        ;

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
        $return = $this->getSwagPropertyAnnotations($entity);

        foreach ($entity->getProperties() as $attribute) {
            $name = $attribute->getName();
            if (isset($return[$name])) {
                continue;
            }

            $return[$name] = $this->getSchemaProperty($attribute);
        }

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
     * @param PropertyDecorator $property
     * @return SchemaProperty
     */
    private function getSchemaProperty(PropertyDecorator $property) : SchemaProperty
    {
        $isReadOnlyField = in_array($property->getName(), self::READ_ONLY_FIELDS);
        $isDateTimeField = in_array($property->getType(), self::DATETIME_TYPES);

        $schemaProperty = new SchemaProperty();
        $schemaProperty
            ->setName($property->getName())
            ->setType(DataTypeConversion::convert($property->getType()))
            ->setReadOnly(($property->isPrimaryKey() || ($isReadOnlyField && $isDateTimeField)))
            ->setRequired($this->isAttributeRequired($property))
        ;

        return $schemaProperty;
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

       foreach ($swagEntityAttributes as $swagEntityAttribute) {
           $return[$swagEntityAttribute->name] = (new SchemaProperty())
               ->setName($swagEntityAttribute->name)
               ->setDescription($swagEntityAttribute->description)
               ->setType($swagEntityAttribute->type)
               ->setReadOnly($swagEntityAttribute->readOnly)
               ->setWriteOnly($swagEntityAttribute->writeOnly)
               ->setRequired($swagEntityAttribute->required)
           ;
       }

        return $return;
    }

    /**
     * @param EntityDecorator $entity
     * @return bool
     */
    private function isSwaggable(EntityDecorator $entity) : bool
    {
        $annotations = AnnotationUtility::getClassAnnotationsFromInstance($entity->getEntity());

        foreach ($annotations as $annotation) {
            if ($annotation instanceof SwagEntity) {
                return $annotation->isVisible;
            }
        }

        return true;
    }

    /**
     * @param PropertyDecorator $property
     * @return bool
     */
    private function isAttributeRequired(PropertyDecorator $property) : bool
    {
        if (!$this->validator) {
            return false;
        }

        $validationSet = $this->validator->field($property->getName());
        if (!$validationSet->isEmptyAllowed()) {
            return true;
        }

        return false;
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
            return null;
        }

        return $validator;
    }
}