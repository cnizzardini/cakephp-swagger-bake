<?php

namespace SwaggerBake\Lib;

use Cake\Core\Configure;
use LogicException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Configuration
 * @package SwaggerBake\Lib
 */
class Configuration
{
    /** @var array  */
    private $configs = [];

    /** @var string  */
    private $root = '';

    public function __construct($config = [], $root = APP)
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
                'requestAccepts' => ['application/x-www-form-urlencoded'],
                'responseContentTypes' => ['application/json'],
                'namespaces' => [
                    'controllers' => ['\App\\'],
                    'entities' => ['\App\\'],
                    'tables' => ['\App\\']
                ]
            ],
            Configure::read('SwaggerBake')
        );
    }

    /**
     * @param string $var
     * @return mixed
     */
    public function get(string $var)
    {
        if (!isset($this->configs[$var])) {
            throw new LogicException("Configuration does not exist for `$var` in Configuration::configs");
        }

        return $this->configs[$var];
    }

    /**
     * @return string
     */
    public function getYml() : string
    {
        return $this->root . $this->get('yml');
    }

    /**
     * @return string
     */
    public function getJson() : string
    {
        return $this->root . $this->get('json');
    }

    /**
     * @return string
     */
    public function getPrefix() : string
    {
        return $this->get('prefix');
    }

    /**
     * @return string
     */
    public function getWebPath() : string
    {
        return $this->get('webPath');
    }

    /**
     * @return bool
     */
    public function getHotReload() : bool
    {
        return (bool) $this->get('hotReload');
    }

    /**
     * @return array
     */
    public function getNamespaces() : array
    {
        return $this->get('namespaces');
    }

    /**
     * @return array
     */
    public function getParsedYml() : array
    {
        return Yaml::parseFile($this->getYml());
    }

    /**
     * @return mixed|string
     */
    public function getTitleFromYml()
    {
        $yml = $this->getParsedYml();
        return isset($yml['info']['title']) ? $yml['info']['title'] : '';
    }

    /**
     * @return string
     */
    public function getDocType() : string
    {
        return strtolower($this->get('docType'));
    }

    /**
     * @return string
     */
    public function getExceptionSchema() : string
    {
        return $this->get('exceptionSchema');
    }

    /**
     * @param string|null $doctype
     * @return string
     */
    public function getLayout(?string $doctype = null) : string
    {
        $doctype = empty($doctype) ? $this->getDocType() : $doctype;
        if ($doctype == 'redoc') {
            return 'SwaggerBake.redoc';
        }
        return 'SwaggerBake.default';
    }

    /**
     * @param string|null $doctype
     * @return string
     */
    public function getView(?string $doctype = null) : string
    {
        $doctype = empty($doctype) ? $this->getDocType() : $doctype;
        if ($doctype == 'redoc') {
            return 'SwaggerBake.Swagger/redoc';
        }
        return 'SwaggerBake.Swagger/index';
    }

    /**
     * @return array
     */
    public function getRequestAccepts() : array
    {
        return $this->get('requestAccepts');
    }

    /**
     * @return array
     */
    public function getResponseContentTypes() : array
    {
        return $this->get('responseContentTypes');
    }
}