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
        if (!in_array($schemaType, ['array', 'object', 'collection'])) {
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
     * @param \SwaggerBake\Lib\OpenApi\Schema $schema Schema
     * @return array
     */
    private function buildDiscriminators(Schema $schema): array
    {
        $items = [];
        if ($schema->getAllOf()) {
            $items['allOf'] = $schema->getAllOf();
        }
        if ($schema->getAnyOf()) {
            $items['anyOf'] = $schema->getAnyOf();
        }
        if ($schema->getOneOf()) {
            $items['oneOf'] = $schema->getOneOf();
        }
        if ($schema->getNot()) {
            $items['not'] = $schema->getNot();
        }

        return $items;
    }
}
