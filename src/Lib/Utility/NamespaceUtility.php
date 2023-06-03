<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Utility;

use Cake\Cache\Engine\NullEngine;
use Mouf\Composer\ClassNameMapper;
use SwaggerBake\Lib\Configuration;
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
        $namespaces = $config->getNamespaces();

        foreach ($namespaces['entities'] ?? [] as $namespace) {
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
        $namespaces = $config->getNamespaces();

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

        foreach ($namespaces as $namespace) {
            if (str_starts_with($namespace, '\\')) {
                $namespace = substr($namespace, 1);
            }

            if (!str_ends_with($namespace, '\\')) {
                $namespace .= '\\';
            }

            $namespace = str_replace('\\\\', '\\', $namespace);
            $namespace .= $ns;

            $classes = array_merge(
                $classes,
                \MixerApi\Core\Utility\NamespaceUtility::findClasses($namespace)
            );
        }

        return $classes;
    }
}
