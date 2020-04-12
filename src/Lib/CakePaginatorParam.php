<?php


namespace SwaggerBake\Lib;


use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;

class CakePaginatorParam
{
    public function getQueryParameters() : array
    {
        $paginators = [
            'page' => 'integer',
            'limit' => 'integer',
            'rows' => 'integer',
            'sort' => 'string',
            'direction' => 'string'
        ];

        $parameter = new Parameter();
        $parameter
            ->setAllowEmptyValue(true)
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