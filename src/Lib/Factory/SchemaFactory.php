<?php


namespace SwaggerBake\Lib\Factory;

use SwaggerBake\Lib\Model\ExpressiveModel;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Utility\DataTypeConversion;

class SchemaFactory
{
    public function create(ExpressiveModel $model) : ?Schema
    {
        $schema = new Schema();
        $schema
            ->setName($model->getName())
            ->setType('object')
            ->setRequired($this->getRequiredAttributes($model))
            ->setProperties($this->getProperties($model))
        ;
        return $schema;
    }

    private function getProperties(ExpressiveModel $model) : array
    {
        $return = [];

        foreach ($model->getAttributes() as $attribute) {
            $name = $attribute->getName();

            $property = new SchemaProperty();
            $property
                ->setName($name)
                ->setType(DataTypeConversion::convert($attribute->getType()))
                ->setReadOnly($attribute->isPrimaryKey())
            ;
            $return[$name] = $property->toArray();
        }

        return $return;
    }

    private function getRequiredAttributes(ExpressiveModel $model) : array
    {
        return [];
        /*
        return array_filter($model->getAttributes(), function ($attribute) {

        });
        */
    }
}