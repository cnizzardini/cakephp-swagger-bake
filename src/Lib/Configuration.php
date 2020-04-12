<?php

namespace SwaggerBake\Lib;

use Cake\Core\Configure;
use LogicException;

class Configuration
{
    public function __construct()
    {
        $this->configs = array_merge(
            [
                'hotReload' => false
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

    public function getYml()
    {
        return ROOT .$this->get('yml');
    }

    public function getJson()
    {
        return ROOT . $this->get('json');
    }

    public function getPrefix()
    {
        return $this->get('prefix');
    }

    public function getWebPath()
    {
        return $this->get('webPath');
    }

    public function getHotReload()
    {
        return $this->get('hotReload');
    }
}