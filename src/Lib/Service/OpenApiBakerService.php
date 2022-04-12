<?php

namespace SwaggerBake\Lib\Service;

use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Swagger;

class OpenApiBakerService
{
    private array $warnings = [];

    /**
     * Creates the OpenAPI json file.
     *
     * @param Swagger $swagger
     * @param string $filePath
     *
     * @return string
     * @throws SwaggerBakeRunTimeException
     */
    public function bake(Swagger $swagger, string $filePath): string
    {
        $swagger->writeFile($filePath);
        foreach ($swagger->getOperationsWithNoHttp20x() as $operation) {
            $this->warnings[] = 'Operation ' . $operation->getOperationId() . ' does not have a HTTP 20x response';
        }

        return $filePath;
    }

    /**
     * @return string[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }
}