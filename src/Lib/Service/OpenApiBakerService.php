<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Service;

use SwaggerBake\Lib\Swagger;

class OpenApiBakerService
{
    private array $warnings = [];

    /**
     * Creates the OpenAPI json file.
     *
     * @param \SwaggerBake\Lib\Swagger $swagger Swagger
     * @param string $filePath The file path to write the openapi json to
     * @return string
     * @throws \SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException
     * @throws \ReflectionException
     */
    public function bake(Swagger $swagger, string $filePath): string
    {
        $swagger->build()->writeFile($filePath);
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
