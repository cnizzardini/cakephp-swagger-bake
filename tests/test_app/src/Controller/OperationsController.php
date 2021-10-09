<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Controller;

use SwaggerBake\Lib\Attribute\OpenApiOperation;

class OperationsController extends AppController
{
    #[OpenApiOperation(isVisible: false)]
    public function isVisible()
    {

    }

    #[OpenApiOperation(tagNames: ['1','2','3','4'])]
    public function tagNames()
    {

    }
}