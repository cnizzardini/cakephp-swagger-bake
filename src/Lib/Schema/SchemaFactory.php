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
use SwaggerBake\Lib\Annotation\SwagEntityAttribute;
use SwaggerBake\Lib\Attribute\AttributeInstance;
use SwaggerBake\Lib\Attribute\OpenApiSchema;
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
    private Validator $validator;

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
     * Creates an instance of Schema for an ModelDecorator, returns null if the Entity is set to invisible
     *
     * @param \SwaggerBake\Lib\Model\ModelDecorator $modelDecorator ModelDecorator
     * @param int $propertyType see public constants for options
     * @return \SwaggerBake\Lib\OpenApi\Schema|null
     * @throws \ReflectionException
     */
    public function create(ModelDecorator $modelDecorator, int $propertyType = 6): ?Schema
    {
        $reflection = new ReflectionClass($modelDecorator->getModel()->getEntity());
        $openApiSchema = (new AttributeInstance($reflection, OpenApiSchema::class))->createOne();

        if ($openApiSchema instanceof OpenApiSchema && $openApiSchema->isVisible === false) {
            return null;
        }

        $schema = $this
            ->createSchema($modelDecorator->getModel(), $propertyType)
            ->setIsPublic($openApiSchema->isPublic ?? true)
            ->setDescription($openApiSchema->description);

        EventManager::instance()->dispatch(
            new Event('SwaggerBake.Schema.created', $schema, [
                'modelDecorator' => $modelDecorator,
            ])
        );

        return $schema;
    }

    /**
     * Same as create() except this does not check SwagEntity options or dispatch an event and will always create a
     * Schema. Using create(), its possible null is returned based on SwagEntity options.
     *
     * @param \SwaggerBake\Lib\Model\ModelDecorator $modelDecorator ModelDecorator
     * @param int $propertyType see public constants for options
     * @return \SwaggerBake\Lib\OpenApi\Schema
     * @throws \ReflectionException
     */
    public function createAlways(ModelDecorator $modelDecorator, int $propertyType = 6): Schema
    {
        return $this->createSchema($modelDecorator->getModel(), $propertyType);
    }

    /**
     * @param \MixerApi\Core\Model\Model $model Model instance
     * @param int $propertyType see public constants for options
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function createSchema(Model $model, int $propertyType = 6): Schema
    {
        $this->validator = $this->getValidator($model);

        $docBlock = $this->getDocBlock($model->getEntity());

        $properties = $this->getProperties($model, $propertyType, $docBlock);

        $schema = (new Schema())
            ->setName((new ReflectionClass($model->getEntity()))->getShortName())
            ->setType('object')
            ->setProperties($properties);

        if (empty($schema->getDescription())) {
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
