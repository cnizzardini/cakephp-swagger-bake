<?php


namespace SwaggerBake\Lib\Factory;

use Cake\Routing\Route\Route;
use Cake\Utility\Inflector;
use phpDocumentor\Reflection\DocBlock;
use ReflectionMethod;
use phpDocumentor\Reflection\DocBlockFactory;
use SwaggerBake\Lib\CakePaginatorParam;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;

/**
 * Class SwaggerPath
 */
class PathFactory
{
    private $route;
    private $prefix = '';
    private $dockBlock;
    private $namespace = '';

    public function __construct(Route $route, Configuration $config)
    {
        $this->namespace = $config->getNamespace();
        $this->route = $route;
        $this->prefix = $config->getPrefix();
        $this->dockBlock = $this->getDocBlock();
    }

    public function create() : ?Path
    {
        $path = new Path();
        $defaults = (array) $this->route->defaults;

        if (empty($defaults['_method'])) {
            return null;
        }

        foreach ((array) $defaults['_method'] as $method) {
            $path
                ->setType(strtolower($method))
                ->setPath($this->createPath())
                ->setOperationId($this->route->getName())
                ->setSummary($this->dockBlock ? $this->dockBlock->getSummary() : '')
                ->setTags([
                    Inflector::humanize(Inflector::underscore($defaults['controller']))
                ])
                ->setParameters($this->createParameters())
            ;
        }

        return $path;
    }

    private function createPath() : string
    {
        $pieces = array_map(
            function ($piece) {
                if (substr($piece, 0, 1) == ':') {
                    return '{' . str_replace(':', '', $piece) . '}';
                }
                return $piece;
            },
            explode('/', $this->route->template)
        );

        $length = strlen($this->prefix);

        return substr(implode('/', $pieces), $length);
    }

    private function createParameters() : array
    {
        return array_merge(
            $this->getPathParameters(),
            $this->getQueryParameters()
        );
    }

    private function getPathParameters() : array
    {
        $return = [];

        $pieces = explode('/', $this->route->template);
        $results = array_filter($pieces, function ($piece) {
           return substr($piece, 0, 1) == ':' ? true : null;
        });

        if (empty($results)) {
            return $return;
        }

        foreach ($results as $result) {

            $schema = new Schema();
            $schema
                ->setType('string')
            ;

            $name = strtolower($result);

            if (substr($name, 0, 1) == ':') {
                $name = substr($name, 1);
            }

            $parameter = new Parameter();
            $parameter
                ->setName($name)
                ->setAllowEmptyValue(false)
                ->setDeprecated(false)
                ->setRequired(true)
                ->setIn('path')
                ->setSchema($schema)
            ;
            $return[] = $parameter;
        }

        return $return;
    }

    private function getDocBlock() : ?DocBlock
    {
        $defaults = (array) $this->route->defaults;
        $className = $defaults['controller'] . 'Controller';
        $methodName = $defaults['action'];

        $controller = $this->namespace . 'Controller\\' . $className;
        $instance = new $controller;

        try {
            $reflectionMethod = new ReflectionMethod(get_class($instance), $methodName);
        } catch (\Exception $e) {
            return null;
        }

        $docFactory = DocBlockFactory::createInstance();
        return $docFactory->create($reflectionMethod->getDocComment());
    }

    private function getQueryParameters() : array
    {
        $return = [];

        if ($this->dockBlock == null) {
            return $return;
        }

        foreach ($this->dockBlock->getTags() as $tag) {
            if ($tag->getName() == 'SwagPaginator') {
                $return = array_merge($return, (new CakePaginatorParam())->getQueryParameters());
            }
            if ($tag->getName() == 'SwaqQuery') {
                //$return = array_merge($return, []);
            }
        }

        return $return;
    }
}
