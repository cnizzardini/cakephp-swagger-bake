<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use Cake\Core\Configure;
use Cake\Log\Log;
use LogicException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Configuration
 *
 * @package SwaggerBake\Lib
 */
class Configuration
{
    /**
     * @var array
     */
    private $configs = [];

    /**
     * @var string
     */
    private $root = '';

    /**
     * @param array $config SwaggerBake configurations (useful for unit tests mainly). Default: []
     * @param string $root The application ROOT (useful for unit tests mainly). Default: ROOT
     */
    public function __construct($config = [], $root = ROOT)
    {
        $this->root = $root;

        $defaultConfig = [
            'docType' => 'swagger',
            'hotReload' => false,
            'exceptionSchema' => 'Exception',
            'requestAccepts' => [
                'application/x-www-form-urlencoded',
                'application/json',
                'application/xml',
            ],
            'responseContentTypes' => [
                'application/json',
                'application/xml',
            ],
            'namespaces' => [
                'controllers' => ['\App\\'],
                'entities' => ['\App\\'],
                'tables' => ['\App\\'],
            ],
            'jsonOptions' => JSON_PRETTY_PRINT,
        ];

        if (!empty($config)) {
            $this->configs = $config + $defaultConfig;

            return;
        }

        $this->configs = array_merge(
            $defaultConfig,
            Configure::read('SwaggerBake') ?? []
        );
    }

    /**
     * @param string $var The Configuration property name
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
    public function getYml(): string
    {
        return $this->root . $this->get('yml');
    }

    /**
     * @return string
     */
    public function getJson(): string
    {
        return $this->root . $this->get('json');
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->get('prefix');
    }

    /**
     * @return string
     */
    public function getWebPath(): string
    {
        return $this->get('webPath');
    }

    /**
     * @return bool
     * @deprecated this method will be deprecated
     * @SuppressWarnings(PHPMD)
     */
    public function getHotReload(): bool
    {
        $deprecationMsg = 'SwaggerBake: getHotReload() in Configuration will be deprecated, use isHotReload()';
        Log::warning($deprecationMsg);
        deprecationWarning($deprecationMsg);

        return $this->isHotReload();
    }

    /**
     * @return bool
     */
    public function isHotReload(): bool
    {
        return (bool)$this->get('hotReload');
    }

    /**
     * @return array
     */
    public function getNamespaces(): array
    {
        $namespaces = $this->get('namespaces');
        foreach ($namespaces as $k => $ns) {
            $namespaces[$k] = array_unique($ns);
        }

        return $namespaces;
    }

    /**
     * @return array
     */
    public function getParsedYml(): array
    {
        return Yaml::parseFile($this->getYml());
    }

    /**
     * @return mixed|string
     */
    public function getTitleFromYml()
    {
        $yml = $this->getParsedYml();

        return $yml['info']['title'] ?? '';
    }

    /**
     * @return string
     */
    public function getDocType(): string
    {
        return strtolower($this->get('docType'));
    }

    /**
     * @return string
     */
    public function getExceptionSchema(): string
    {
        return $this->get('exceptionSchema');
    }

    /**
     * @param string|null $doctype The layout type ("redoc" or null for Swagger). Default: null
     * @return string
     */
    public function getLayout(?string $doctype = null): string
    {
        $doctype = empty($doctype) ? $this->getDocType() : $doctype;
        if ($doctype == 'redoc') {
            return 'SwaggerBake.redoc';
        }

        return 'SwaggerBake.default';
    }

    /**
     * @param string|null $doctype The documentation type ("redoc" or null for Swagger). Default: null
     * @return string
     */
    public function getView(?string $doctype = null): string
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
    public function getRequestAccepts(): array
    {
        return $this->get('requestAccepts');
    }

    /**
     * @return array
     */
    public function getResponseContentTypes(): array
    {
        return $this->get('responseContentTypes');
    }

    /**
     * @param mixed $property Configuration property
     * @param mixed $value Configuration value
     * @return void
     */
    public function set($property, $value): void
    {
        if (!isset($this->configs[$property])) {
            throw new LogicException("Configuration does not exist for `$property` in Configuration::configs");
        }
        $this->configs[$property] = $value;
    }
}
