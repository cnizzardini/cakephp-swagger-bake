<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Schema;

use Cake\Validation\Validator;
use MixerApi\Core\Model\ModelProperty;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Property as PropertyTag;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Utility\DataTypeConversion;

/**
 * Creates an instance of SchemaProperty using your Cake projects Schema, Validation Rules, and `@property` tag in
 * the Entity for the description.
 *
 * @internal
 */
class SchemaPropertyFactory
{
    /**
     * @param \Cake\Validation\Validator $validator Validator
     * @param \phpDocumentor\Reflection\DocBlock|null $docBlock a DocBlock instance of the Entity
     */
    public function __construct(
        private Validator $validator,
        private ?DocBlock $docBlock = null
    ) {
    }

    /**
     * Creates an instance of SchemaProperty
     *
     * @param \MixerApi\Core\Model\ModelProperty $property ModelProperty
     * @return \SwaggerBake\Lib\OpenApi\SchemaProperty
     */
    public function create(ModelProperty $property): SchemaProperty
    {
        $schemaProperty = new SchemaProperty();
        $schemaProperty
            ->setName($property->getName())
            ->setType(DataTypeConversion::toType($property->getType()))
            ->setFormat(DataTypeConversion::toFormat($property->getType()))
            ->setIsHidden($property->isHidden());

        /*
         * Convert `json` types to oneOf: object or array
         */
        if ($schemaProperty->getType() === 'json') {
            $schemaProperty->setType(null);
            $schemaProperty->setOneOf([['type' => 'object'], ['type' => 'array', 'items' => []]]);
        }

        /*
         * Per OpenAPI spec, only one of `writeOnly` and `readOnly` may be set to true.
         *
         * @link https://swagger.io/specification/
         */
        if ($property->isAccessible() && $property->isHidden()) {
            $schemaProperty->setWriteOnly(true);
        }
        if (!$property->isAccessible() && !$property->isHidden()) {
            $schemaProperty->setReadOnly(true);
        }

        $schemaProperty = (new SchemaPropertyValidation($this->validator, $schemaProperty, $property))
            ->withValidations();

        $schemaProperty = (new SchemaPropertyFormat($this->validator, $schemaProperty, $property))
            ->withFormat();

        $propertyTag = $this->findPropertyTag($property);
        if ($propertyTag instanceof PropertyTag) {
            $description = $propertyTag->getDescription();
            $schemaProperty->setDescription($description->getBodyTemplate());
        }

        return $schemaProperty;
    }

    /**
     * @param \MixerApi\Core\Model\ModelProperty $property instance of ModelProperty
     * @return \phpDocumentor\Reflection\DocBlock\Tags\Property|null
     */
    private function findPropertyTag(ModelProperty $property): ?PropertyTag
    {
        if (!$this->docBlock instanceof DocBlock) {
            return null;
        }

        /** @var \phpDocumentor\Reflection\DocBlock\Tags\Property[] $results */
        $results = array_filter(
            $this->docBlock->getTagsByName('property'),
            function (PropertyTag $tag) use ($property) {
                return $tag->getVariableName() === $property->getName();
            }
        );

        return !empty($results) ? reset($results) : null;
    }
}
