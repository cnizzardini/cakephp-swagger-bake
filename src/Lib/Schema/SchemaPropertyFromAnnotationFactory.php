<?php

namespace SwaggerBake\Lib\Schema;

use SwaggerBake\Lib\Annotation\SwagEntityAttribute;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

/**
 * Class SchemaPropertyFromAnnotationFactory
 * @package SwaggerBake\Lib\Schema
 */
class SchemaPropertyFromAnnotationFactory
{
    /**
     * Creates an instance of SchemaProperty from SwagEntityAttribute annotation
     *
     * @param SwagEntityAttribute $attribute
     * @return SchemaProperty
     */
    public function create(SwagEntityAttribute $attribute) : SchemaProperty
    {
        $schemaProperty = (new SchemaProperty())
            ->setName($attribute->name)
            ->setDescription($attribute->description ?? '')
            ->setType($attribute->type)
            ->setReadOnly($attribute->readOnly ?? false)
            ->setWriteOnly($attribute->writeOnly ?? false)
            ->setRequired($attribute->required ?? false)
            ->setEnum($attribute->enum ?? [])
        ;

        $properties = [
            'maxLength',
            'minLength',
            'pattern',
            'maxItems',
            'minItems',
            'uniqueItems',
            'maxProperties',
            'exclusiveMaximum',
            'exclusiveMinimum',
            'uniqueItems',
            'maxProperties',
            'minProperties',
        ];

        foreach ($properties as $property) {
            if (is_null($attribute->{$property})) {
                continue;
            }
            $setterMethod = 'set' . ucfirst($property);
            $schemaProperty->{$setterMethod}($attribute->{$property});
        }

        return $schemaProperty;
    }
}