<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Utility;

use LogicException;
use Mouf\Composer\ClassNameMapper;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use Cake\Cache\Engine\NullEngine;
use TheCodingMachine\ClassExplorer\Glob\GlobClassExplorer;

/**
 * Class NamespaceUtility
 *
 * @package SwaggerBake\Lib\Utility
 */
class NamespaceUtility
{
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

    /**
     * Performs a recursive check for the given namespaces and returns an array of classes
     *
     * @param string[] $namespaces array of namespaces strings (e.g. ['\App\\'])
     * @param string $ns an additional name to append to each namespace as part of the search (e.g. Controller)
     * @example getClasses(['\App\\'], 'Controller') to search for \App\Controller classes
     * @return string[]
     * @throws \Exception
     */
    public static function getClasses(array $namespaces, string $ns = ''): array
    {
        $classes = [];

        $classNameMapper = ClassNameMapper::createFromComposerFile(null, null, true);

        foreach ($namespaces as $namespace) {
            if (substr($namespace, 0, 1) === '\\') {
                $namespace = substr($namespace, 1);
            }

            if (substr($namespace, 0, -1) !== '\\') {
                $namespace .= '\\';
            }

            $namespace = str_replace('\\\\', '\\', $namespace);
            $namespace .= $ns;


            $explorer = new GlobClassExplorer($namespace, new NullEngine(), 0, $classNameMapper);

            $classes = array_merge(
                $classes,
                array_keys($explorer->getClassMap())
            );
        }

        return $classes;
    }
}
