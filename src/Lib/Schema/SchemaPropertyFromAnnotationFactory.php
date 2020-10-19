<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Schema;

use SwaggerBake\Lib\Annotation\AbstractSchemaProperty;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

/**
 * Class SchemaPropertyFromAnnotationFactory
 *
 * @package SwaggerBake\Lib\Schema
 */
class SchemaPropertyFromAnnotationFactory
{
    /**
     * Creates an instance of SchemaProperty from SwagEntityAttribute annotation
     *
     * @param \SwaggerBake\Lib\Annotation\AbstractSchemaProperty $attribute Annotation extending AbstractSchemaProperty
     * @return \SwaggerBake\Lib\OpenApi\SchemaProperty
     */
    public function create(AbstractSchemaProperty $attribute): SchemaProperty
    {
        $schemaProperty = (new SchemaProperty())
            ->setName($attribute->name)
            ->setDescription($attribute->description ?? '')
            ->setType($attribute->type)
            ->setReadOnly($attribute->readOnly ?? false)
            ->setWriteOnly($attribute->writeOnly ?? false)
            ->setRequired($attribute->required ?? false)
            ->setEnum($attribute->enum ?? []);

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
            'example',
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
