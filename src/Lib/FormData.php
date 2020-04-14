<?php

namespace SwaggerBake\Lib;

use SwaggerBake\Lib\Annotation as SwagAnnotation;

class FormData extends AbstractParameter
{
    public function getSchemaProperties() : array
    {
        $return = [];

        foreach ($this->getMethods() as $method) {
            $annotations = $this->reader->getMethodAnnotations($method);
            if (empty($annotations)) {
                continue;
            }

            foreach ($annotations as $annotation) {
                if ($annotation instanceof SwagAnnotation\SwagForm) {
                    $return = array_merge(
                        $return,
                        [
                            (new SwagAnnotation\SwagFormHandler())->getSchemaProperty($annotation)
                        ]
                    );
                }
            }
        }

        return $return;
    }
}