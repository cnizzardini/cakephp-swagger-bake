<?php

namespace SwaggerBake\Lib\Annotation;

use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;

class SwagPaginatorHandler
{
    /**
     * @param SwagPaginator $annotation
     * @return array
     */
    public function getQueryParameters(SwagPaginator $annotation) : array
    {
        $paginators = [
            'page' => 'integer',
            'limit' => 'integer',
            'sort' => 'string',
            'direction' => 'string'
        ];

        $parameter = (new Parameter())
            ->setAllowEmptyValue(false)
            ->setDeprecated(false)
            ->setRequired(false)
            ->setIn('query');

        $return = [];
        foreach ($paginators as $name => $type) {
            $param = clone $parameter;
            $return[] = $param->setName($name)->setSchema((new Schema())->setType($type));
        }
        return $return;
    }
}