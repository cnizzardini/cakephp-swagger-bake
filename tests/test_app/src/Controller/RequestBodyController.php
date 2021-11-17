<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Controller;

use SwaggerBake\Lib\Attribute\OpenApiRequestBody;

class RequestBodyController extends AppController
{
    #[OpenApiRequestBody(mimeTypes: ['text/plain'])]
    public function textPlain(): void
    {

    }

    #[OpenApiRequestBody(mimeTypes: ['text/plain', 'application/xml'])]
    public function multipleMimeTypes(): void
    {

    }

    #[OpenApiRequestBody(ref: '')]
    public function useConfigDefaults(): void
    {

    }
}