<?php

namespace SwaggerBake\Lib\Operation;

use SwaggerBake\Lib\Annotation\SwagPaginator;
use SwaggerBake\Lib\Annotation\SwagQuery;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;

class OperationQueryParameter
{
    /**
     * @param Operation $operation
     * @param array $annotations
     * @return Operation
     */
    public function getOperationWithQueryParameters(Operation $operation, array $annotations) : Operation
    {
        $swagPaginator = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagPaginator;
        });

        if (!empty($swagPaginator)) {

            $parameter = (new Parameter())
                ->setAllowEmptyValue(false)
                ->setDeprecated(false)
                ->setRequired(false)
                ->setIn('query');

            $params = ['page' => 'integer', 'limit' => 'integer', 'sort' => 'string', 'direction' => 'string'];
            foreach ($params as $name => $type) {
                $operation->pushParameter(
                    (clone $parameter)->setName($name)->setSchema((new Schema())->setType($type))
                );
            }
        }

        $swagQueries = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagQuery;
        });

        foreach ($swagQueries as $annotation) {
            $parameter = (new Parameter())
                ->setName($annotation->name)
                ->setDescription($annotation->description)
                ->setAllowEmptyValue(false)
                ->setDeprecated(false)
                ->setRequired($annotation->required)
                ->setIn('query')
                ->setSchema((new Schema())->setType($annotation->type))
            ;

            $operation->pushParameter($parameter);
        }

        return $operation;
    }
}