<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Controller;

use SwaggerBake\Lib\Annotation as Swag;
use SwaggerBake\Lib\Extension\CakeSearch\Annotation\SwagSearch;

class SwagRequestBodyContentController extends AppController
{
    /**
     * @Swag\SwagRequestBodyContent(refEntity="", mimeTypes={"text/plain"})
     */
    public function textPlain()
    {

    }

    /**
     * @Swag\SwagRequestBodyContent(refEntity="", mimeTypes={"text/plain","application/xml"})
     */
    public function multipleMimeTypes()
    {

    }

    /**
     * @Swag\SwagRequestBodyContent(refEntity="")
     */
    public function useConfigDefaults()
    {

    }
}