<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use SwaggerBake\Lib\Annotation\SwagDto;
use SwaggerBake\Lib\Annotation\SwagPaginator;
use SwaggerBake\Lib\Annotation\SwagQuery;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Factory\ParameterFromAnnotationFactory;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;

/**
 * Class OperationQueryParameter
 *
 * @package SwaggerBake\Lib\Operation
 */
class OperationQueryParameter
{
    /**
     * Adds query parameters to the Operation
     *
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param array $annotations Array of annotation objects
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    public function getOperationWithQueryParameters(Operation $operation, array $annotations): Operation
    {
        if ($operation->getHttpMethod() != 'GET') {
            return $operation;
        }

        $operation = $this->withSwagPaginator($operation, $annotations);
        $operation = $this->withSwagQuery($operation, $annotations);
        try {
            $operation = $this->withSwagDto($operation, $annotations);
        } catch (\ReflectionException $e) {
            throw new SwaggerBakeRunTimeException('ReflectionException: ' . $e->getMessage());
        }

        return $operation;
    }

    /**
     * Adds CakePHP Paginator query parameters to the Operation
     *
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param array $annotations Array of annotation objects
     * @see https://book.cakephp.org/4/en/controllers/components/pagination.html
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    private function withSwagPaginator(Operation $operation, array $annotations): Operation
    {
        $swagPaginator = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagPaginator;
        });

        if (empty($swagPaginator)) {
            return $operation;
        }

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

        return $operation;
    }

    /**
     * Adds query parameters from SwagPaginator to the Operation
     *
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param array $annotations An array of annotation objects
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    private function withSwagQuery(Operation $operation, array $annotations): Operation
    {
        $swagQueries = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagQuery;
        });

        $factory = new ParameterFromAnnotationFactory();
        foreach ($swagQueries as $annotation) {
            $operation->pushParameter($factory->create($annotation));
        }

        return $operation;
    }

    /**
     * Adds query parameters from SwagDto to the Operation
     *
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param array $annotations An array of annotation objects
     * @return \SwaggerBake\Lib\OpenApi\Operation
     * @throws \ReflectionException
     */
    private function withSwagDto(Operation $operation, array $annotations): Operation
    {
        $swagDtos = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagDto;
        });

        if (empty($swagDtos)) {
            return $operation;
        }

        $dto = reset($swagDtos);
        $fqns = $dto->class;

        if (!class_exists($fqns)) {
            return $operation;
        }

        $parameters = (new DtoParser($fqns))->getParameters();
        foreach ($parameters as $parameter) {
            $operation->pushParameter($parameter);
        }

        return $operation;
    }
}
