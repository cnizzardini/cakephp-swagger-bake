<?php

namespace SwaggerBake\Lib;

use SwaggerBake\Lib\Annotation as SwagAnnotation;
use SwaggerBake\Lib\OpenApi\Parameter;

class HeaderParameter extends AbstractParameter
{
    /**
     * @return Parameter[]
     */
    public function getHeaderParameters() : array
    {
        $return = [];

        foreach ($this->getMethods() as $method) {
            $annotations = $this->reader->getMethodAnnotations($method);
            if (empty($annotations)) {
                continue;
            }
            foreach ($annotations as $annotation) {
                if ($annotation instanceof SwagAnnotation\SwagHeader) {
                    $return = array_merge(
                        $return,
                        [(new SwagAnnotation\SwagHeaderHandler())->getHeaderParameters($annotation)]
                    );
                }
            }
        }

        return $return;
    }
}