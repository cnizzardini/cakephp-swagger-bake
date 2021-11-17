<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use InvalidArgumentException;

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
}
