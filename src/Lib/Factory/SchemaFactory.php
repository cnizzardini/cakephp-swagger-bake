<?php


namespace SwaggerBake\Lib\Factory;

use Cake\Utility\Inflector;
use Cake\Validation\Validator;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use SwaggerBake\Lib\Annotation\SwagEntity;
use SwaggerBake\Lib\Annotation\SwagEntityAttribute;
use SwaggerBake\Lib\Annotation\SwagEntityAttributeHandler;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Model\ExpressiveAttribute;
use SwaggerBake\Lib\Model\ExpressiveModel;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Utility\AnnotationUtility;
use SwaggerBake\Lib\Utility\DataTypeConversion;

class SchemaFactory
{
    private const READ_ONLY_FIELDS = ['created','modified'];
    private const DATETIME_TYPES = ['date','datetime','timestamp'];
    private $validator;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    public function create(ExpressiveModel $model) : ?Schema
    {
        if (!$this->isSwaggable($model)) {
            return null;
        }

        $this->validator = $this->getValidator($model->getName());

        $docBlock = $this->getDocBlock($model);

        $properties = $this->getProperties($model);

        $schema = new Schema();
        $schema
            ->setName($model->getName())
            ->setDescription($docBlock ? $docBlock->getSummary() : '')
            ->setType('object')
            ->setProperties($properties)
        ;

        $requiredProperties = array_filter($properties, function ($property) {
            return $property->isRequired();
        });

        $schema->setRequired($requiredProperties);

        return $schema;
    }

    private function getProperties(ExpressiveModel $model) : array
    {
        $return = $this->getSwagPropertyAnnotations($model);

        foreach ($model->getAttributes() as $attribute) {
            $name = $attribute->getName();
            if (isset($return[$name])) {
                continue;
            }

            $return[$name] = $this->getSchemaProperty($attribute);
        }

        return $return;
    }

    private function getDocBlock(ExpressiveModel $model) : ?DocBlock
    {
        $entity = $this->getEntityFromNamespaces($model->getName());

        try {
            $instance = new $entity;
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

    private function getEntityFromNamespaces(string $className) : ?string
    {
        $namespaces = $this->config->getNamespaces();

        if (!isset($namespaces['entities']) || !is_array($namespaces['entities'])) {
            throw new SwaggerBakeRunTimeException(
                'Invalid configuration, missing SwaggerBake.namespaces.entities'
            );
        }

        foreach ($namespaces['entities'] as $namespace) {
            $entity = $namespace . 'Model\Entity\\' . $className;
            if (class_exists($entity, true)) {
                return $entity;
            }
        }

        return null;
    }

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

    private function getSchemaProperty(ExpressiveAttribute $attribute) : SchemaProperty
    {
        $isReadOnlyField = in_array($attribute->getName(), self::READ_ONLY_FIELDS);
        $isDateTimeField = in_array($attribute->getType(), self::DATETIME_TYPES);

        $property = new SchemaProperty();
        $property
            ->setName($attribute->getName())
            ->setType(DataTypeConversion::convert($attribute->getType()))
            ->setReadOnly(($attribute->isPrimaryKey() || ($isReadOnlyField && $isDateTimeField)))
            ->setRequired($this->isAttributeRequired($attribute))
        ;

        return $property;
    }

    private function getSwagPropertyAnnotations(ExpressiveModel $model) : array
    {
        $return = [];

        $entity = $this->getEntityFromNamespaces($model->getName());
        $annotations = AnnotationUtility::getClassAnnotations($entity);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof SwagEntityAttribute) {
                $schemaProperty = (new SwagEntityAttributeHandler())->getSchemaProperty($annotation);
                $return[$schemaProperty->getName()] = $schemaProperty;
            }
        }

        return $return;
    }

    private function isSwaggable(ExpressiveModel $model) : bool
    {
        $entity = $this->getEntityFromNamespaces($model->getName());
        $annotations = AnnotationUtility::getClassAnnotations($entity);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof SwagEntity) {
                return $annotation->isVisible;
            }
        }

        return true;
    }

    private function isAttributeRequired(ExpressiveAttribute $attribute) : bool
    {
        if (!$this->validator) {
            return false;
        }

        $validationSet = $this->validator->field($attribute->getName());
        if (!$validationSet->isEmptyAllowed()) {
            return true;
        }

        return false;
    }

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