<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use InvalidArgumentException;
use SwaggerBake\Lib\OpenApi\Schema;

trait MediaTypeTrait
{
    /**
     * @param string $schemaType must be array or object
     * @return void
     */
    private function validateSchemaType(string $schemaType): void
    {
        if (!in_array($schemaType, ['array', 'object'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Argument must be array or object but was given schemaType `%s`. If you\'re using the ' .
                    'SwagResponseSchema annotation, try defining schemaType.',
                    $schemaType
                )
            );
        }
    }

    /**
     * Validates schema is either an instance of Schema or a string, otherwise throws an exception
     *
     * @param \SwaggerBake\Lib\OpenApi\Schema|string $schema instance of Schema or an OpenAPI $ref string
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validateSchema($schema): void
    {
        if (!$schema instanceof Schema && !is_string($schema)) {
            throw new InvalidArgumentException(
                '$schema argument must be instance of Schema or an OpenAPI $ref string'
            );
        }
    }
}
