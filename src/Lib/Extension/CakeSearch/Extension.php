<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Extension\CakeSearch;

use Cake\Core\Plugin;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\Table;
use Doctrine\Common\Annotations\AnnotationRegistry;
use ReflectionClass;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Extension\CakeSearch\Annotation\SwagSearch;
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
     * @return void
     */
    public function loadAnnotations(): void
    {
        AnnotationRegistry::loadAnnotationClass(SwagSearch::class);
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
        /** @var \SwaggerBake\Lib\OpenApi\Operation $operation */
        $operation = $event->getSubject();

        $annotations = $event->getData('methodAnnotations');

        $results = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagSearch;
        });

        if (empty($results)) {
            return $operation;
        }

        $swagSearch = reset($results);

        $operation = $this->getOperationWithQueryParameters($operation, $swagSearch);

        return $operation;
    }

    /**
     * Returns an Operation instance after applying query parameters
     *
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param \SwaggerBake\Lib\Extension\CakeSearch\Annotation\SwagSearch $swagSearch SwagSearch
     * @return \SwaggerBake\Lib\OpenApi\Operation
     * @throws \ReflectionException
     * @throws \SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException
     */
    private function getOperationWithQueryParameters(Operation $operation, SwagSearch $swagSearch): Operation
    {
        if ($operation->getHttpMethod() != 'GET') {
            return $operation;
        }

        $tableFqns = $swagSearch->tableClass;

        if (!class_exists($tableFqns)) {
            throw new SwaggerBakeRunTimeException("tableClass `$tableFqns` does not exist");
        }

        $filters = $this->getFilterDecorators(new $tableFqns(), $swagSearch);

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
        $parameter = new Parameter();
        $parameter->setName($filter->getName())
            ->setIn('query')
            ->setDescription($filter->getComparison() . ' ' . $filter->getName());

        $schema = new Schema();

        switch ($filter->getComparison()) {
            default:
                $schema->setType('string');
        }

        return $parameter->setSchema($schema);
    }

    /**
     * @param \Cake\ORM\Table $table Table
     * @param \SwaggerBake\Lib\Extension\CakeSearch\Annotation\SwagSearch $swagSearch SwagSearch
     * @return \SwaggerBake\Lib\Extension\CakeSearch\FilterDecorator[]
     * @throws \ReflectionException
     */
    private function getFilterDecorators(Table $table, SwagSearch $swagSearch): array
    {
        $manager = $this->getSearchManager($table, $swagSearch);

        $collections = $this->getCollections($manager);

        if (!isset($collections[$swagSearch->collection])) {
            return [];
        }

        $reflection = new ReflectionClass($collections[$swagSearch->collection]);
        $property = $reflection->getProperty('_filters');
        $property->setAccessible(true);
        $filters = $property->getValue($collections[$swagSearch->collection]);

        $decoratedFilters = [];

        foreach ($filters as $filter) {
            $decoratedFilters[] = (new FilterDecorator($filter));
        }

        return $decoratedFilters;
    }

    /**
     * @param \Cake\ORM\Table $table Table
     * @param \SwaggerBake\Lib\Extension\CakeSearch\Annotation\SwagSearch $swagSearch SwagSearch
     * @return \Search\Manager
     * @throws \ReflectionException
     */
    private function getSearchManager(Table $table, SwagSearch $swagSearch): \Search\Manager
    {
        $table->find('search', [
            'search' => [],
            'collection' => $swagSearch->collection,
        ]);
        $search = $table->getBehavior('Search');

        $reflection = new ReflectionClass($search);
        $property = $reflection->getProperty('_manager');
        $property->setAccessible(true);

        return $property->getValue($search);
    }

    /**
     * @param \Search\Manager $manager Search\Manager
     * @return array
     * @throws \ReflectionException
     */
    private function getCollections(\Search\Manager $manager): array
    {
        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('_collections');
        $property->setAccessible(true);

        return $property->getValue($manager);
    }
}
