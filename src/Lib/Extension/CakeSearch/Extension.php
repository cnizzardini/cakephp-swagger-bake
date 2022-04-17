<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Extension\CakeSearch;

use Cake\Core\Plugin;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\Table;
use ReflectionMethod;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Extension\CakeSearch\Attribute\OpenApiSearch;
use SwaggerBake\Lib\Extension\ExtensionInterface;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;

/**
 * Class Extension
 *
 * @package SwaggerBake\Lib\Extension\FriendsOfCakeSearch
 */
class Extension implements ExtensionInterface
{
    /**
     * @return void
     * @SuppressWarning(PHPMD)
     */
    public function registerListeners(): void
    {
        EventManager::instance()
            ->on('SwaggerBake.Operation.created', function (Event $event) {
                $operation = $this->getOperation($event);
            });
    }

    /**
     * @return bool
     */
    public function isSupported(): bool
    {
        return in_array('Search', Plugin::loaded());
    }

    /**
     * Returns an Operation instance after adding search operators (if possible)
     *
     * @param \Cake\Event\Event $event Event
     * @return \SwaggerBake\Lib\OpenApi\Operation
     * @throws \ReflectionException
     */
    public function getOperation(Event $event): Operation
    {
        $operation = $event->getSubject();
        if (!$operation instanceof Operation) {
            throw new SwaggerBakeRunTimeException(
                sprintf(
                    'Extension `%s` could not be run because the subject must be an instance of `%s`',
                    self::class,
                    Operation::class
                )
            );
        }

        if ($operation->getHttpMethod() != 'GET') {
            return $operation;
        }

        /** @var \ReflectionMethod $refMethod */
        $refMethod = $event->getData('reflectionMethod');
        if (!$refMethod instanceof ReflectionMethod) {
            return $operation;
        }

        $openApiSearch = (new AttributeFactory($refMethod, OpenApiSearch::class))->createOneOrNull();
        if (!$openApiSearch instanceof OpenApiSearch) {
            return $operation;
        }

        return $this->getOperationWithQueryParameters($operation, $openApiSearch);
    }

    /**
     * Returns an Operation instance after applying query parameters
     *
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param \SwaggerBake\Lib\Extension\CakeSearch\Attribute\OpenApiSearch $openApiSearch OpenApiSearch
     * @return \SwaggerBake\Lib\OpenApi\Operation
     * @throws \ReflectionException
     * @throws \SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException
     */
    private function getOperationWithQueryParameters(Operation $operation, OpenApiSearch $openApiSearch): Operation
    {
        $tableFqn = $openApiSearch->tableClass;
        if (!class_exists($tableFqn)) {
            throw new SwaggerBakeRunTimeException(
                sprintf(
                    'Unable to build OpenApiSearch because tableClass `%s` does not exist',
                    $tableFqn
                )
            );
        }

        $filters = $this->getFilterDecorators(new $tableFqn(), $openApiSearch);
        foreach ($filters as $filter) {
            $operation->pushParameter($this->createParameter($filter));
        }

        return $operation;
    }

    /**
     * @param \SwaggerBake\Lib\Extension\CakeSearch\FilterDecorator $filter FilterDecorator
     * @return \SwaggerBake\Lib\OpenApi\Parameter
     */
    private function createParameter(FilterDecorator $filter): Parameter
    {
        return (new Parameter(in: 'query', name: $filter->getName()))
            ->setSchema(
                (new Schema())->setType('string')
            );
    }

    /**
     * @param \Cake\ORM\Table $table Table
     * @param \SwaggerBake\Lib\Extension\CakeSearch\Attribute\OpenApiSearch $openApiSearch OpenApiSearch
     * @return \SwaggerBake\Lib\Extension\CakeSearch\FilterDecorator[]
     * @throws \ReflectionException
     */
    private function getFilterDecorators(Table $table, OpenApiSearch $openApiSearch): array
    {
        $decoratedFilters = [];

        $manager = $this->getSearchManager($table, $openApiSearch);

        $filters = $manager->getFilters($openApiSearch->collection);

        if (empty($filters)) {
            return $decoratedFilters;
        }

        foreach ($filters as $filter) {
            $decoratedFilters[] = (new FilterDecorator($filter));
        }

        return $decoratedFilters;
    }

    /**
     * @param \Cake\ORM\Table $table Table
     * @param \SwaggerBake\Lib\Extension\CakeSearch\Attribute\OpenApiSearch $openApiSearch OpenApiSearch
     * @return \Search\Manager
     */
    private function getSearchManager(Table $table, OpenApiSearch $openApiSearch): \Search\Manager
    {
        $table->find('search', [
            'search' => [],
            'collection' => $openApiSearch->collection,
        ]);

        /** @var \Search\Model\Behavior\SearchBehavior $search */
        $search = $table->getBehavior('Search');

        return $search->searchManager();
    }
}
