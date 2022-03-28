<?php

namespace Demo\Controller;

use SwaggerBake\Lib\Attribute\OpenApiResponse;

class TestController
{
    /**
     * Just an example of a plugin.
     *
     * @return \Cake\Http\Response
     */
    #[OpenApiResponse(description: 'demo', mimeTypes: ['text/plain'])]
    public function index()
    {

    }
}
