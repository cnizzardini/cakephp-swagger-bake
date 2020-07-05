<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Schema;

use Cake\Validation\Validator;
use SwaggerBake\Lib\Decorator\PropertyDecorator;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Utility\DataTypeConversion;

/**
 * Class SchemaPropertyFactory
 *
 * @package SwaggerBake\Lib\Schema
 *
 * Creates an instance of SchemaProperty using your Cake projects Schema and Validation Rules
 */
class SchemaPropertyFactory
{
    /**
     * @var \Cake\Validation\Validator
     */
    private $validator;

    /**
     * @param \Cake\Validation\Validator $validator Validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Creates an instance of SchemaProperty
     *
     * @param \SwaggerBake\Lib\Decorator\PropertyDecorator $property PropertyDecorator
     * @return \SwaggerBake\Lib\OpenApi\SchemaProperty
     */
    public function create(PropertyDecorator $property): SchemaProperty
    {
        $schemaProperty = new SchemaProperty();
        $schemaProperty
            ->setName($property->getName())
            ->setType(DataTypeConversion::toType($property->getType()))
            ->setFormat(DataTypeConversion::toFormat($property->getType()))
            ->setReadOnly($this->isReadOnly($property));

        return (new SchemaPropertyValidation($this->validator, $schemaProperty, $property))->withValidations();
    }

    /**
     * @param \SwaggerBake\Lib\Decorator\PropertyDecorator $property PropertyDecorator
     * @return bool
     */
    private function isReadOnly(PropertyDecorator $property): bool
    {
        if ($property->isPrimaryKey()) {
            return true;
        }

        $isTimeBehaviorField = in_array($property->getName(), ['created','modified']);
        $isDateTimeField = in_array($property->getType(), ['date','datetime','timestamp']);

        if ($isTimeBehaviorField && $isDateTimeField) {
            return true;
        }

        return false;
    }
}
