<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use SwaggerBake\Lib\Annotation\SwagHeader;
use SwaggerBake\Lib\Factory\ParameterFromAnnotationFactory;
use SwaggerBake\Lib\OpenApi\Operation;

/**
 * Class OperationHeader
 *
 * @package SwaggerBake\Lib\Operation
 */
class OperationHeader
{
    /**
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param array $annotations Array of annotation objects
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    public function getOperationWithHeaders(Operation $operation, array $annotations): Operation
    {
        $swagHeaders = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagHeader;
        });

        $factory = new ParameterFromAnnotationFactory();
        foreach ($swagHeaders as $annotation) {
            $operation->pushParameter($factory->create($annotation));
        }

        return $operation;
    }
}
