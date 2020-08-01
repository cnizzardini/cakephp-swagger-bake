<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Utility;

use LogicException;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;

/**
 * Class NamespaceUtility
 *
 * @package SwaggerBake\Lib\Utility
 */
class NamespaceUtility
{
    /**
     * Gets a controllers FQNS using the controllers short name
     *
     * @param string $className Controller name
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     * @return string|null
     * @throws \SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException
     */
    public static function getControllerFullQualifiedNameSpace(string $className, Configuration $config): ?string
    {
        try {
            $namespaces = $config->getNamespaces();
        } catch (LogicException $e) {
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

    /**
     * Gets a FQNS of an Entity using the entities short name
     *
     * @param string $className Entity class name
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     * @return string|null
     * @throws \SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException
     */
    public static function getEntityFullyQualifiedNameSpace(string $className, Configuration $config): ?string
    {
        try {
            $namespaces = $config->getNamespaces();
        } catch (LogicException $e) {
            throw new SwaggerBakeRunTimeException(
                'Invalid configuration, missing SwaggerBake.namespaces.entities'
            );
        }

        foreach ($namespaces['entities'] as $namespace) {
            $entity = $namespace . 'Model\Entity\\' . $className;
            if (class_exists($entity, true)) {
                return $entity;
            }
        }

        return null;
    }

    /**
     * Gets a FQNS of a Table
     *
     * @param string $className Table class name
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     * @return string|null
     * @throws \SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException
     */
    public static function getTableFullyQualifiedNameSpace(string $className, Configuration $config): ?string
    {
        try {
            $namespaces = $config->getNamespaces();
        } catch (LogicException $e) {
            throw new SwaggerBakeRunTimeException(
                'Invalid configuration, missing SwaggerBake.namespaces.tables'
            );
        }

        foreach ($namespaces['tables'] as $namespace) {
            $table = $namespace . 'Model\Table\\' . $className;
            if (class_exists($table, true)) {
                return $table;
            }
        }

        return null;
    }
}
