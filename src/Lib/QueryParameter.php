<?php

namespace SwaggerBake\Lib;


use SwaggerBake\Lib\Annotation as SwagAnnotation;

class QueryParameter extends AbstractParameter
{
    public function getQueryParameters() : array
    {
        $return = [];

        foreach ($this->getMethods() as $method) {
            $annotations = $this->reader->getMethodAnnotations($method);
            if (empty($annotations)) {
                continue;
            }

            foreach ($annotations as $annotation) {
                if ($annotation instanceof SwagAnnotation\SwagPaginator) {
                    $return = array_merge(
                        $return,
                        (new SwagAnnotation\SwagPaginatorHandler())->getQueryParameters($annotation)
                    );
                }
                if ($annotation instanceof SwagAnnotation\SwagQuery) {
                    $return = array_merge(
                        $return,
                        [
                            (new SwagAnnotation\SwagQueryHandler())->getQueryParameter($annotation)
                        ]
                    );
                }
            }
        }

        return $return;
    }
}