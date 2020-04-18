<?php


namespace SwaggerBake\Lib\Factory;

use Cake\Routing\Route\Route;
use Cake\Utility\Inflector;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionMethod;
use SwaggerBake\Lib\Annotation as SwagAnnotation;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\OpenApi\OperationExternalDoc;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Utility\AnnotationUtility;

/**
 * Class SwaggerPath
 */
class PathFactory
{
    private $route;
    private $prefix = '';
    private $config;

    public function __construct(Route $route, Configuration $config)
    {
        $this->config = $config;
        $this->route = $route;
        $this->prefix = $config->getPrefix();
        $this->dockBlock = $this->getDocBlock();
    }

    /**
     * Creates a Path and returns it
     *
     * @return Path|null
     */
    public function create() : ?Path
    {
        $path = new Path();
        $defaults = (array) $this->route->defaults;

        if (empty($defaults['_method'])) {
            return null;
        }

        if (!$this->isSwaggable($defaults['controller'])) {
            return null;
        }

        foreach ((array) $defaults['_method'] as $method) {
            $path
                ->setType(strtolower($method))
                ->setPath($this->getPathName())
                ->setOperationId($this->route->getName())
                ->setSummary($this->dockBlock ? $this->dockBlock->getSummary() : '')
                ->setDescription($this->dockBlock ? $this->dockBlock->getDescription() : '')
                ->setTags([
                    Inflector::humanize(Inflector::underscore($defaults['controller']))
                ])
                ->setParameters($this->getPathParameters())
                ->setDeprecated($this->isDeprecated())
            ;

            $path = $this->withResponses($path, $defaults['controller'], $defaults['action']);

            $externalDoc = $this->getExternalDoc();
            if ($externalDoc) {
                $path->setExternalDocs($externalDoc);
            }
        }

        return $path;
    }

    private function getPathName() : string
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

    private function withResponses(Path $path, string $className, string $method) : Path
    {
        $className = $className . 'Controller';
        $controller = $this->getControllerFromNamespaces($className);
        $annotations = AnnotationUtility::getMethodAnnotations($controller, $method);

        if (empty($annotations)) {
            return $path;
        }

        $responses = [];

        foreach ($annotations as $annotation) {
            if ($annotation instanceof SwagAnnotation\SwagResponseSchema) {
                $resp = (new SwagAnnotation\SwagResponseSchemaHandler())->getResponse($annotation);
                $responses[$resp->getCode()] = $resp;
            }
        }

        return $path->setResponses($responses);
    }

    private function getDocBlock() : ?DocBlock
    {
        $defaults = (array) $this->route->defaults;

        if (!isset($defaults['controller'])) {
            return null;
        }

        $className = $defaults['controller'] . 'Controller';
        $methodName = $defaults['action'];

        $controller = $this->getControllerFromNamespaces($className);

        if (!class_exists($controller)) {
            return null;
        }

        $instance = new $controller;

        try {
            $reflectionMethod = new ReflectionMethod(get_class($instance), $methodName);
        } catch (\Exception $e) {
            return null;
        }

        $comments = $reflectionMethod->getDocComment();

        if (!$comments) {
            return null;
        }

        $docFactory = DocBlockFactory::createInstance();
        return $docFactory->create($comments);
    }

    private function getControllerFromNamespaces(string $className) : ?string
    {
        $namespaces = $this->config->getNamespaces();

        if (!isset($namespaces['controllers']) || !is_array($namespaces['controllers'])) {
            throw new SwaggerBakeRunTimeException(
                'Invalid configuration, missing SwaggerBake.namespaces.controllers'
            );
        }

        foreach ($namespaces['controllers'] as $namespace) {
            $entity = $namespace . 'Controller\\' . $className;
            if (class_exists($entity, true)) {
                return $entity;
            }
        }

        return null;
    }

    private function isSwaggable(string $className) : bool
    {
        $className = $className . 'Controller';
        $controller = $this->getControllerFromNamespaces($className);

        if (!$controller) {
            return false;
        }

        $annotations = AnnotationUtility::getClassAnnotations($controller);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof SwagAnnotation\SwagPath) {
                return $annotation->isVisible;
            }
        }

        return true;
    }

    private function isDeprecated() : bool
    {
        if (!$this->dockBlock || !$this->dockBlock instanceof DocBlock) {
            return false;
        }

        return $this->dockBlock->hasTag('deprecated');
    }

    private function getExternalDoc() : ?OperationExternalDoc
    {
        if (!$this->dockBlock || !$this->dockBlock instanceof DocBlock) {
            return null;
        }

        if (!$this->dockBlock->hasTag('see')) {
            return null;
        }

        $tags = $this->dockBlock->getTagsByName('see');
        $seeTag = reset($tags);
        $str = $seeTag->__toString();
        $pieces = explode(' ', $str);

        if (!filter_var($pieces[0], FILTER_VALIDATE_URL)) {
            return null;
        }

        $externalDoc = new OperationExternalDoc();
        $externalDoc->setUrl($pieces[0]);

        array_shift($pieces);

        if (!empty($pieces)) {
            $externalDoc->setDescription(implode(' ', $pieces));
        }

        return $externalDoc;
    }
}
