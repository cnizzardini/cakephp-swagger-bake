<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Controller;

use SwaggerBake\Lib\Attribute\OpenApiOperation;

class OperationsController extends AppController
{
    #[OpenApiOperation(isVisible: false)]
    public function isVisible(): void
    {

    }

    #[OpenApiOperation(tagNames: ['1','2','3','4'])]
    public function tagNames(): void
    {

    }

    #[OpenApiOperation(isDeprecated: true)]
    public function deprecated(): void
    {

    }

    #[OpenApiOperation(externalDocs: ['url' => 'http://localhost', 'description' => 'desc...'])]
    public function externalDocs(): void
    {

    }

    #[OpenApiOperation(summary: 'summary...', description: 'desc...')]
    public function descriptions(): void
    {

    }
}