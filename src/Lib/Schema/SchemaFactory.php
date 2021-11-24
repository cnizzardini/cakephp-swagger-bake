<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Schema;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Validation\Validator;
use MixerApi\Core\Model\Model;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use SwaggerBake\Lib\Annotation\SwagEntity;
use SwaggerBake\Lib\Annotation\SwagEntityAttribute;
use SwaggerBake\Lib\Model\ModelDecorator;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Utility\AnnotationUtility;

/**
 * Creates an instance of SwaggerBake\Lib\OpenApi\Schema per OpenAPI specifications
 *
 * @internal
 */
class SchemaFactory
{
    /**
     * @var \Cake\Validation\Validator
     */
    private $validator;

    /**
     * @var int
     */
    public const WRITEABLE_PROPERTIES = 2;

    /**
     * @var int
     */
    public const READABLE_PROPERTIES = 4;

    /**
     * @var int
     */
    public const ALL_PROPERTIES = 6;

    /**
     * Creates an instance of Schema for an ModelDecorator, returns null if the Entity is set to invisible
     *
     * @param \SwaggerBake\Lib\Model\ModelDecorator $modelDecorator ModelDecorator
     * @param int $propertyType see public constants for o
     * @return \SwaggerBake\Lib\OpenApi\Schema|null
     * @throws \ReflectionException
     */
    public function create(ModelDecorator $modelDecorator, int $propertyType = 6): ?Schema
    {
        $model = $modelDecorator->getModel();
        $swagEntity = $this->getSwagEntityAnnotation($model->getEntity());

        if ($swagEntity !== null && $swagEntity->isVisible === false) {
            return null;
        }

        $this->validator = $this->getValidator($model);

        $docBlock = $this->getDocBlock($model->getEntity());

        $properties = $this->getProperties($model, $propertyType, $docBlock);

        $schema = (new Schema())
            ->setName((new ReflectionClass($model->getEntity()))->getShortName())
            ->setDescription($swagEntity->description)
            ->setType('object')
            ->setProperties($properties)
            ->setIsPublic($swagEntity->isPublic);

        if (empty($schema->getDescription())) {
            $schema->setDescription($docBlock ? $docBlock->getSummary() : null);
        }

        $requiredProperties = array_filter($properties, function ($property) {
            return $property->isRequired();
        });

        if (!empty($requiredProperties)) {
            $schema->setRequired(array_keys($requiredProperties));
        }

        EventManager::instance()->dispatch(
            new Event('SwaggerBake.Schema.created', $schema, [
                'modelDecorator' => $modelDecorator,
            ])
        );

        return $schema;
    }

    /**
     * @param \MixerApi\Core\Model\Model $model Model
     * @param int $propertyType see public constants for options
     * @param \phpDocumentor\Reflection\DocBlock|null $docBlock DocBlock instance
     * @return array
     */
    private function getProperties(Model $model, int $propertyType, ?DocBlock $docBlock): array
    {
        $return = [];
        $factory = new SchemaPropertyFactory($this->validator, $docBlock);

        foreach ($model->getProperties() as $property) {
            $return[$property->getName()] = $factory->create($property);
        }

        $return = array_merge($return, $this->getSwagPropertyAnnotations($model));

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
     * @param \Cake\Datasource\EntityInterface $entity EntityInterface
     * @return \phpDocumentor\Reflection\DocBlock|null
     */
    private function getDocBlock(EntityInterface $entity): ?DocBlock
    {
        try {
            $reflectionClass = new ReflectionClass($entity);
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
     * Returns key-value pair of property name => SchemaProperty
     *
     * @param \MixerApi\Core\Model\Model $model Model
     * @return \SwaggerBake\Lib\OpenApi\SchemaProperty[]
     */
    private function getSwagPropertyAnnotations(Model $model): array
    {
        $return = [];

        $annotations = AnnotationUtility::getClassAnnotationsFromInstance($model->getEntity());

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
     * Returns instance of SwagEntity annotation
     *
     * @param \Cake\Datasource\EntityInterface $entity EntityInterface
     * @return \SwaggerBake\Lib\Annotation\SwagEntity
     */
    private function getSwagEntityAnnotation(EntityInterface $entity): SwagEntity
    {
        $annotations = AnnotationUtility::getClassAnnotationsFromInstance($entity);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof SwagEntity) {
                return $annotation;
            }
        }

        return new SwagEntity([]);
    }

    /**
     * Gets the Table classes Validator
     *
     * @param \MixerApi\Core\Model\Model $model Model
     * @return \Cake\Validation\Validator
     */
    private function getValidator(Model $model): Validator
    {
        try {
            $validator = $model->getTable()->validationDefault(new Validator());
        } catch (\Exception $e) {
            return new Validator();
        }

        return $validator;
    }
}
