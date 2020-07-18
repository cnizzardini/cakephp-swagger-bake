<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Controller;

use SwaggerBake\Lib\Annotation as Swag;
use SwaggerBake\Lib\Extension\CakeSearch\Annotation\SwagSearch;

class OperationsController extends AppController
{
    /**
     * @Swag\SwagOperation(isVisible=false)
     */
    public function isVisible()
    {

    }

    /**
     * @Swag\SwagOperation(tagNames={"These","Tags","Are","Might"})
     */
    public function tagNames()
    {

    }
}