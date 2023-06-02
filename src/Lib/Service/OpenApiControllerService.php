<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Service;

use Cake\Http\ServerRequest;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Swagger;
use SwaggerBake\Lib\SwaggerFactory;

class OpenApiControllerService
{
    private Configuration $config;
    private Swagger $swagger;

    /**
     * @param \SwaggerBake\Lib\Configuration|null $config Swagger Configuration, created if null
     * @param \SwaggerBake\Lib\Swagger|null $swagger Swagger, created if null
     * @throws \ReflectionException
     */
    public function __construct(
        ?Configuration $config = null,
        ?Swagger $swagger = null,
    ) {
        $this->config = $config ?? new Configuration();
        $this->swagger = $swagger ?? (new SwaggerFactory($this->config))->create();
    }

    /**
     * Rebuilds OpenAPI if hot reload is enabled and logs warnings if debug is enabled
     *
     * @return void
     * @throws \ReflectionException
     */
    public function build(): void
    {
        if ($this->config->isHotReload()) {
            $output = $this->config->getJson();
            $this->swagger->build()->writeFile($output);
        }
    }

    /**
     * @return \SwaggerBake\Lib\Configuration
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    /**
     * Get the requested document type (swagger or redoc). If none is present in the docType query parameter use
     * the value from swagger_bake config.
     *
     * @param \Cake\Http\ServerRequest $request CakePHP ServerRequest
     * @return string
     */
    public function getDocType(ServerRequest $request): string
    {
        if (empty($request->getQuery('doctype')) || !is_string($request->getQuery('doctype'))) {
            return $this->config->getDocType();
        }

        $docType = h(strtolower($request->getQuery('doctype')));

        return in_array($docType, ['swagger','redoc']) ? $docType : 'swagger';
    }
}
