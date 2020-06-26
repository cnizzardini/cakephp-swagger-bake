<?php

namespace SwaggerBake\Lib\Schema;

use Cake\Validation\Validator;
use SwaggerBake\Lib\Decorator\PropertyDecorator;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Utility\DataTypeConversion;

/**
 * Class SchemaPropertyFactory
 * @package SwaggerBake\Lib\Schema
 *
 * Creates an instance of SchemaProperty using your Cake projects Schema and Validation Rules
 */
class SchemaPropertyFactory
{
    /** @var Validator  */
    private $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param PropertyDecorator $property
     * @return SchemaProperty
     */
    public function create(PropertyDecorator $property) : SchemaProperty
    {
        $schemaProperty = new SchemaProperty();
        $schemaProperty
            ->setName($property->getName())
            ->setType(DataTypeConversion::toType($property->getType()))
            ->setFormat(DataTypeConversion::toFormat($property->getType()))
            ->setReadOnly($this->isReadOnly($property))
        ;

        if (!$this->validator) {
            return $schemaProperty;
        }

        return (new SchemaPropertyValidation($this->validator, $schemaProperty, $property))->withValidations();
    }

    /**
     * @param PropertyDecorator $property
     * @return bool
     */
    private function isReadOnly(PropertyDecorator $property) : bool
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