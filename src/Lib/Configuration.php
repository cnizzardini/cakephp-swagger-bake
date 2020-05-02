<?php

namespace SwaggerBake\Lib;

use Cake\Core\Configure;
use LogicException;
use Symfony\Component\Yaml\Yaml;

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
                'docType' => 'swagger',
                'hotReload' => false,
                'exceptionSchema' => 'Exception',
                'namespaces' => [
                    'controllers' => ['\App\\'],
                    'entities' => ['\App\\'],
                    'tables' => ['\App\\']
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

    public function getParsedYml() : array
    {
        return Yaml::parseFile($this->getYml());
    }

    public function getTitleFromYml()
    {
        $yml = $this->getParsedYml();
        return isset($yml['info']['title']) ? $yml['info']['title'] : '';
    }

    public function getDocType() : string
    {
        return strtolower($this->get('docType'));
    }

    public function getExceptionSchema() : string
    {
        return $this->get('exceptionSchema');
    }

    public function getLayout(?string $doctype = null) : string
    {
        $doctype = empty($doctype) ? $this->getDocType() : $doctype;
        if ($doctype == 'redoc') {
            return 'SwaggerBake.redoc';
        }
        return 'SwaggerBake.default';
    }

    public function getView(?string $doctype = null) : string
    {
        $doctype = empty($doctype) ? $this->getDocType() : $doctype;
        if ($doctype == 'redoc') {
            return 'SwaggerBake.Swagger/redoc';
        }
        return 'SwaggerBake.Swagger/index';
    }
}