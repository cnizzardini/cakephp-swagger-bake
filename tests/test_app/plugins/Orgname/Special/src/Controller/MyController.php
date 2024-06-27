<?php

namespace Orgname\Special\Controller;

use SwaggerBake\Lib\Attribute\OpenApiResponse;

class MyController
{
    /**
     * Just an example of a plugin.
     *
     * @return \Cake\Http\Response
     */
    #[OpenApiResponse(description: 'my', mimeTypes: ['text/plain'])]
    public function index()
    {

    }
}
