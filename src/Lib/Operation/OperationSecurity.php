<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use ReflectionMethod;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Attribute\OpenApiSecurity;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\PathSecurity;

/**
 * Class OperationSecurity
 *
 * @package SwaggerBake\Lib\Operation
 */
class OperationSecurity
{
    /**
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param \ReflectionMethod|null $refMethod ReflectionMethod or null
     */
    public function __construct(
        private Operation $operation,
        private ?ReflectionMethod $refMethod,
    ) {
    }

    /**
     * Gets an Operation instance after applying security
     *
     * @return \SwaggerBake\Lib\OpenApi\Operation
     * @throws \ReflectionException
     */
    public function getOperationWithSecurity(): Operation
    {
        $this->assignOpenApiSecurity();

        return $this->operation;
    }

    /**
     * Assigns OpenApiSecurity attribute
     *
     * @return void
     * @throws \ReflectionException
     */
    private function assignOpenApiSecurity(): void
    {
        if (!$this->refMethod instanceof ReflectionMethod) {
            return;
        }

        $securities = (new AttributeFactory($this->refMethod, OpenApiSecurity::class))->createMany();
        if (empty($securities)) {
            return;
        }

        foreach ($securities as $sec) {
            $this->operation->pushSecurity(
                (new PathSecurity())
                    ->setName($sec->name)
                    ->setScopes($sec->scopes),
            );
        }
    }
}
