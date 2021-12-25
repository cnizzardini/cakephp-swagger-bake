<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\OpenApiExceptionSchemaInterface;

class OpenApiExceptionSchemaInterfaceImplementation implements OpenApiExceptionSchemaInterface
{
    /**
     * @inheritDoc
     */
    public static function getExceptionCode(): string
    {
        return '400';
    }

    /**
     * @inheritDoc
     */
    public static function getExceptionDescription(): string
    {
        return 'A Description';
    }

    /**
     * @inheritDoc
     */
    public static function getExceptionSchema(): Schema
    {
        return (new Schema())
            ->setTitle('MyException')
            ->setProperties([
                (new SchemaProperty())->setType('string')->setName('code')->setExample('400'),
                (new SchemaProperty())->setType('string')->setName('message')->setExample('error')
            ]);
    }
}