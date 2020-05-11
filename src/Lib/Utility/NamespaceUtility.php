<?php

namespace SwaggerBake\Lib\Utility;

use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;

class NamespaceUtility
{
    /**
     * @param string $className
     * @param Configuration $config
     * @return string|null
     */
    public static function getController(string $className, Configuration $config) : ?string
    {
        $namespaces = $config->getNamespaces();

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
}