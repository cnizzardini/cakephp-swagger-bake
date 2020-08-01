<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Controller;

use SwaggerBake\Lib\Annotation as Swag;
use SwaggerBake\Lib\Extension\CakeSearch\Annotation\SwagSearch;

class OperationPathController extends AppController
{
    /**
     * @Swag\SwagPathParameter(name="id", type="integer", format="int64", description="ID")
     */
    public function pathParameter($id = null)
    {

    }
}