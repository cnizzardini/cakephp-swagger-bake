<?php

namespace SwaggerBake\Lib;

use Cake\Core\Configure;
use LogicException;

class Configuration
{
    private $configs = [];
    private $root = '';

    public function __construct($config = [], $root = ROOT)
    {
        $this->root = $root;

        if (!empty($config)) {
            $this->configs = $config;
            return;
        }

        $this->configs = array_merge(
            [
                'hotReload' => false,
                'namespaces' => [
                    'controllers' => ['\App\\'],
                    'entities' => ['\App\\'],
                ]
            ],
            Configure::read('SwaggerBake')
        );
    }

    public function get(string $var)
    {
        if (!isset($this->configs[$var])) {
            throw new LogicException("Configuration does not exist, given $var");
        }

        return $this->configs[$var];
    }

    public function getYml() : string
    {
        return $this->root . $this->get('yml');
    }

    public function getJson() : string
    {
        return $this->root . $this->get('json');
    }

    public function getPrefix() : string
    {
        return $this->get('prefix');
    }

    public function getWebPath() : string
    {
        return $this->get('webPath');
    }

    public function getHotReload() : bool
    {
        return (bool) $this->get('hotReload');
    }

    public function getNamespaces() : array
    {
        return $this->get('namespaces');
    }
}