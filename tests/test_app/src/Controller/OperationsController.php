<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Controller;

use SwaggerBake\Lib\Attribute\OpenApiOperation;
use SwaggerBake\Lib\Attribute\OpenApiPath;
use SwaggerBake\Lib\Attribute\OpenApiResponse;

#[OpenApiPath(tags: ['Test', 'Another Test'])]
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

    /**
     * @link https://github.com/cnizzardini/cakephp-swagger-bake/issues/525
     * @throws \Cake\Http\Exception\BadRequestException
     */
    #[OpenApiResponse(schemaType: 'string', statusCode: '400', description: 'This should take precedence over throw tag')]
    public function throwPrecedence()
    {

    }
}