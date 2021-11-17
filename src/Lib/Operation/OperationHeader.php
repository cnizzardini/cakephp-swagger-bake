<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use ReflectionMethod;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Attribute\OpenApiHeader;
use SwaggerBake\Lib\OpenApi\Operation;

class OperationHeader
{
    /**
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param \ReflectionMethod|null $refMethod ReflectionMethod or null
     * @return \SwaggerBake\Lib\OpenApi\Operation
     * @throws \ReflectionException
     */
    public function getOperationWithHeaders(Operation $operation, ?ReflectionMethod $refMethod = null): Operation
    {
        if (!$refMethod instanceof ReflectionMethod) {
            return $operation;
        }

        /** @var \SwaggerBake\Lib\Attribute\OpenApiHeader[] $headers */
        $headers = (new AttributeFactory($refMethod, OpenApiHeader::class))->createMany();
        if (!count($headers)) {
            return $operation;
        }

        foreach ($headers as $attribute) {
            $operation->pushParameter($attribute->createParameter());
        }

        return $operation;
    }
}
