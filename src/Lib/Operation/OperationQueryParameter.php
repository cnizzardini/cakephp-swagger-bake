<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use Cake\Controller\Controller;
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
     * @var \SwaggerBake\Lib\OpenApi\Operation
     */
    private $operation;

    /**
     * Array of annotations
     *
     * @var array
     */
    private $annotations;

    /**
     * @var \Cake\Controller\Controller
     */
    private $controller;

    /**
     * @var \SwaggerBake\Lib\OpenApi\Schema
     */
    private $schema;

    /**
     * Adds query parameters to the Operation
     *
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation instance of the Operation
     * @param array $annotations an array of annotation objects
     * @param \Cake\Controller\Controller $controller instance of the Controller
     * @param \SwaggerBake\Lib\OpenApi\Schema $schema instance of Schema
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    public function __construct(
        Operation $operation,
        array $annotations,
        Controller $controller,
        ?Schema $schema = null
    ) {
        $this->operation = $operation;
        $this->annotations = $annotations;
        $this->controller = $controller;
        $this->schema = $schema;
    }

    /**
     * Adds query parameters to the Operation
     *
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    public function getOperationWithQueryParameters(): Operation
    {
        if ($this->operation->getHttpMethod() != 'GET') {
            return $this->operation;
        }

        $this->definePagination();
        $this->defineQueryParametersFromAnnotations();

        try {
            $this->defineDataTransferObjectFromAnnotations();
        } catch (\ReflectionException $e) {
            throw new SwaggerBakeRunTimeException('ReflectionException: ' . $e->getMessage());
        }

        return $this->operation;
    }

    /**
     * Adds CakePHP Paginator query parameters to the Operation
     *
     * @return void
     */
    private function definePagination()
    {
        $results = array_filter($this->annotations, function ($annotation) {
            return $annotation instanceof SwagPaginator;
        });

        if (empty($results)) {
            return;
        }

        /** @var \SwaggerBake\Lib\Annotation\SwagPaginator $swagPaginator */
        $swagPaginator = reset($results);

        $parameter = (new Parameter())
            ->setAllowEmptyValue(false)
            ->setDeprecated(false)
            ->setRequired(false)
            ->setIn('query');

        $params = [
            'page' => ['type' => 'integer'],
            'limit' => ['type' => 'integer'],
            'sort' => ['type' => 'string'],
            'direction' => ['type' => 'string', 'enum' => ['asc','desc']],
        ];

        foreach ($params as $name => $param) {
            $schema = (new Schema())->setType($param['type']);
            $schema->setEnum($param['enum'] ?? []);
            $this->operation->pushParameter((clone $parameter)->setName($name)->setSchema($schema));
        }

        if ($swagPaginator->useSortTextInput === true) {
            return;
        }

        $parameter = $this->operation->getParameterByTypeAndName('query', 'sort');

        if (!empty($swagPaginator->sortEnum)) {
            $schema = $parameter->getSchema()->setEnum($swagPaginator->sortEnum);
            $this->operation->pushParameter($parameter->setSchema($schema));

            return;
        }

        if (isset($this->controller->paginate['sortableFields'])) {
            $schema = $parameter->getSchema()->setEnum($this->controller->paginate['sortableFields']);
            $this->operation->pushParameter($parameter->setSchema($schema));

            return;
        }

        if ($this->schema != null && is_array($this->schema->getProperties())) {
            $enumList = [];
            foreach ($this->schema->getProperties() as $property) {
                $enumList[] = $property->getName();
            }
            $schema = $parameter->getSchema()->setEnum($enumList);
            $this->operation->pushParameter($parameter->setSchema($schema));

            return;
        }
    }

    /**
     * Adds query parameters from SwagPaginator to the Operation
     *
     * @return void
     */
    private function defineQueryParametersFromAnnotations(): void
    {
        $swagQueries = array_filter($this->annotations, function ($annotation) {
            return $annotation instanceof SwagQuery;
        });

        $factory = new ParameterFromAnnotationFactory();
        foreach ($swagQueries as $annotation) {
            $this->operation->pushParameter($factory->create($annotation));
        }
    }

    /**
     * Adds query parameters from SwagDto to the Operation
     *
     * @return void
     * @throws \ReflectionException
     */
    private function defineDataTransferObjectFromAnnotations(): void
    {
        $swagDtos = array_filter($this->annotations, function ($annotation) {
            return $annotation instanceof SwagDto;
        });

        if (empty($swagDtos)) {
            return;
        }

        $dto = reset($swagDtos);
        $fqns = $dto->class;

        if (!class_exists($fqns)) {
            return;
        }

        $parameters = (new DtoParser($fqns))->getParameters();
        foreach ($parameters as $parameter) {
            $this->operation->pushParameter($parameter);
        }
    }
}
