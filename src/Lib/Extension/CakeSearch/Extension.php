<?php

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
 * @package SwaggerBake\Lib\Extension\FriendsOfCakeSearch
 */
class Extension implements ExtensionInterface
{
    /**
     * @return void
     */
    public function registerListeners() : void
    {
        EventManager::instance()
            ->on('SwaggerBake.Operation.created', function (Event $event) {
                $operation = $this->getOperation($event);
            });
    }

    /**
     * @return bool
     */
    public function isSupported() : bool
    {
        return in_array('Search', Plugin::loaded());
    }

    /**
     * @return void
     */
    public function loadAnnotations() : void
    {
        AnnotationRegistry::loadAnnotationClass(SwagSearch::class);
    }

    /**
     * Returns an Operation instance after adding search operators (if possible)
     *
     * @param Event $event
     * @return Operation
     * @throws \ReflectionException
     */
    public function getOperation(Event $event) : Operation
    {
        /** @var Operation $operation */
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
     * @param Operation $operation
     * @param SwagSearch $swagSearch
     * @return Operation
     * @throws \ReflectionException
     * @throws SwaggerBakeRunTimeException
     */
    private function getOperationWithQueryParameters(Operation $operation, SwagSearch $swagSearch) : Operation
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
     * @param FilterDecorator $filter
     * @return Parameter
     */
    private function createParameter(FilterDecorator $filter) : Parameter
    {
        $parameter = new Parameter();
        $parameter->setName($filter->getName())
            ->setIn('query')
            ->setDescription($filter->getComparison() . ' ' . $filter->getName());

        $schema = new Schema();

        switch ($filter->getComparison())
        {
            default:
                $schema->setType('string');
        }

        return $parameter->setSchema($schema);
    }

    /**
     * @param Table $table
     * @param SwagSearch $swagSearch
     * @return FilterDecorator[]
     * @throws \ReflectionException
     */
    private function getFilterDecorators(Table $table, SwagSearch $swagSearch) : array
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
     * @param Table $table
     * @param SwagSearch $swagSearch
     * @return \Search\Manager
     * @throws \ReflectionException
     */
    private function getSearchManager(Table $table, SwagSearch $swagSearch) : \Search\Manager
    {
        $table->find('search',[
            'search' => [],
            'collection' => $swagSearch->collection
        ]);
        $search = $table->getBehavior('Search');

        $reflection = new ReflectionClass($search);
        $property = $reflection->getProperty('_manager');
        $property->setAccessible(true);

        return $property->getValue($search);
    }

    /**
     * @param \Search\Manager $manager
     * @return array
     * @throws \ReflectionException
     */
    private function getCollections(\Search\Manager $manager) : array
    {
        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('_collections');
        $property->setAccessible(true);

        return $property->getValue($manager);
    }
}