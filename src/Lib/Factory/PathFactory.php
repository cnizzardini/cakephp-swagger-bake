<?php

namespace SwaggerBake\Lib\Factory;

use Cake\Utility\Inflector;
use Exception;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionMethod;
use SwaggerBake\Lib\Annotation as SwagAnnotation;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\ExceptionHandler;
use SwaggerBake\Lib\Model\ExpressiveRoute;
use SwaggerBake\Lib\OpenApi\OperationExternalDoc;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Response;
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

    public function __construct(ExpressiveRoute $route, Configuration $config)
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

        if (empty($this->route->getMethods())) {
            return null;
        }

        if (!$this->isControllerVisible($this->route->getController())) {
            return null;
        }

        foreach ($this->route->getMethods() as $method) {

            $methodAnnotations = $this->getMethodAnnotations(
                $this->route->getController(),
                $this->route->getAction()
            );

            if (!$this->isMethodVisible($methodAnnotations)) {
                continue;
            }

            $path
                ->setType(strtolower($method))
                ->setPath($this->getPathName())
                ->setOperationId($this->route->getName())
                ->setSummary($this->dockBlock ? $this->dockBlock->getSummary() : '')
                ->setDescription($this->dockBlock ? $this->dockBlock->getDescription() : '')
                ->setTags([
                    Inflector::humanize(Inflector::underscore($this->route->getController()))
                ])
                ->setParameters($this->getPathParameters())
                ->setDeprecated($this->isDeprecated())
            ;

            $path = $this->withResponses($path, $methodAnnotations);
            $path = $this->withRequestBody($path, $methodAnnotations);
            $path = $this->withExternalDoc($path);
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
            explode('/', $this->route->getTemplate())
        );

        if ($this->prefix == '/') {
            return implode('/', $pieces);
        }

        return substr(
            implode('/', $pieces),
            strlen($this->prefix)
        );
    }

    private function getPathParameters() : array
    {
        $return = [];

        $pieces = explode('/', $this->route->getTemplate());
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

    private function getMethodAnnotations(string $className, string $method) : array
    {
        $className = $className . 'Controller';
        $controller = $this->getControllerFromNamespaces($className);
        return AnnotationUtility::getMethodAnnotations($controller, $method);
    }

    private function withResponses(Path $path, array $annotations) : Path
    {
        if (!empty($annotations)) {
            foreach ($annotations as $annotation) {
                if ($annotation instanceof SwagAnnotation\SwagResponseSchema) {
                    $path->pushResponse((new SwagAnnotation\SwagResponseSchemaHandler())->getResponse($annotation));
                }
            }
        }

        if (!$this->dockBlock || !$this->dockBlock->hasTag('throws')) {
            return $path;
        }

        $throws = $this->dockBlock->getTagsByName('throws');

        foreach ($throws as $throw) {
            $exception = new ExceptionHandler($throw->getType()->__toString());
            $path->pushResponse(
                (new Response())->setCode($exception->getCode())->setDescription($exception->getMessage())
            );
        }

        return $path;
    }

    private function withRequestBody(Path $path, array $annotations) : Path
    {
        if (empty($annotations)) {
            return $path;
        }

        foreach ($annotations as $annotation) {
            if ($annotation instanceof SwagAnnotation\SwagRequestBody) {
                $requestBody = (new SwagAnnotation\SwagRequestBodyHandler())->getResponse($annotation);
            }
        }

        if (!isset($requestBody)) {
            return $path;
        }

        foreach ($annotations as $annotation) {
            if ($annotation instanceof SwagAnnotation\SwagRequestBodyContent) {
                $requestBody->pushContent(
                    (new SwagAnnotation\SwagRequestBodyContentHandler())->getContent($annotation)
                );
            }
        }

        if (empty($requestBody->getContent())) {
            return $path->setRequestBody($requestBody);
        }

        return $path->setRequestBody($requestBody);
    }

    private function getDocBlock() : ?DocBlock
    {
        if (empty($this->route->getController())) {
            return null;
        }

        $className = $this->route->getController() . 'Controller';
        $methodName = $this->route->getAction();

        $controller = $this->getControllerFromNamespaces($className);

        if (!class_exists($controller)) {
            return null;
        }

        $instance = new $controller;

        try {
            $reflectionMethod = new ReflectionMethod(get_class($instance), $methodName);
        } catch (Exception $e) {
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

    private function isControllerVisible(string $className) : bool
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

    private function isMethodVisible(array $annotations) : bool
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof SwagAnnotation\SwagOperation) {
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

    private function withExternalDoc(Path $path) : Path
    {
        if (!$this->dockBlock || !$this->dockBlock instanceof DocBlock) {
            return $path;
        }

        if (!$this->dockBlock->hasTag('see')) {
            return $path;
        }

        $tags = $this->dockBlock->getTagsByName('see');
        $seeTag = reset($tags);
        $str = $seeTag->__toString();
        $pieces = explode(' ', $str);

        if (!filter_var($pieces[0], FILTER_VALIDATE_URL)) {
            return $path;
        }

        $externalDoc = new OperationExternalDoc();
        $externalDoc->setUrl($pieces[0]);

        array_shift($pieces);

        if (!empty($pieces)) {
            $externalDoc->setDescription(implode(' ', $pieces));
        }

        return $path->setExternalDocs($externalDoc);
    }
}
