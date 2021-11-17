<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Controller;

use SwaggerBake\Lib\Attribute\OpenApiPathParam;

class OperationPathController extends AppController
{
    #[OpenApiPathParam(name: 'id', type: 'integer', format: 'int64', description: 'ID')]
    public function pathParameter(string $id = null): void
    {

    }
}