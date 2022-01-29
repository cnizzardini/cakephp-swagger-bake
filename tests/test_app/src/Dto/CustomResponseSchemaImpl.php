<?php

namespace SwaggerBakeTest\App\Dto;

use SwaggerBake\Lib\OpenApi\CustomSchemaInterface;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

class CustomResponseSchemaImpl implements CustomSchemaInterface
{
    /**
     * @inheritDoc
     */
    public static function getOpenApiSchema(): Schema
    {
        return (new Schema())
            ->setTitle('Custom')
            ->setProperties([
                (new SchemaProperty())->setType('string')->setName('name')->setExample('Paul'),
                (new SchemaProperty())->setType('integer')->setName('age')->setExample(32)
            ]);
    }
}