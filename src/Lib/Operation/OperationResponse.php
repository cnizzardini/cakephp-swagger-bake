<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use SwaggerBake\Lib\Annotation\SwagResponseSchema;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\MediaType\Generic;
use SwaggerBake\Lib\MediaType\HalJson;
use SwaggerBake\Lib\MediaType\JsonLd;
use SwaggerBake\Lib\MediaType\Xml as XmlMedia;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Response;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\Xml;
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Swagger;

/**
 * Class OperationResponse
 *
 * @package SwaggerBake\Lib\Operation
 */
class OperationResponse
{
    /**
     * @var \SwaggerBake\Lib\Swagger
     */
    private $swagger;

    /**
     * @var \SwaggerBake\Lib\Configuration
     */
    private $config;

    /**
     * @var \SwaggerBake\Lib\OpenApi\Operation
     */
    private $operation;

    /**
     * @var \phpDocumentor\Reflection\DocBlock
     */
    private $doc;

    /**
     * @var array
     */
    private $annotations;

    /**
     * @var \SwaggerBake\Lib\Route\RouteDecorator
     */
    private $route;

    /**
     * @var \SwaggerBake\Lib\OpenApi\Schema|null
     */
    private $schema;

    /**
     * @param \SwaggerBake\Lib\Swagger $swagger Swagger
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param array $annotations An array of annotation objects
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
     * @param \SwaggerBake\Lib\OpenApi\Schema|null $schema Schema
     */
    public function __construct(
        Swagger $swagger,
        Configuration $config,
        Operation $operation,
        array $annotations,
        RouteDecorator $route,
        ?Schema $schema
    ) {
        $this->swagger = $swagger;
        $this->config = $config;
        $this->operation = $operation;
        $this->annotations = $annotations;
        $this->route = $route;
        $this->schema = $schema;
    }

    /**
     * Gets an Operation with Responses
     *
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    public function getOperationWithResponses(): Operation
    {
        $this->assignFromAnnotations();
        $this->assignFromCrudActions();
        $this->assignDefaultResponses();

        return $this->operation;
    }

    /**
     * Set Responses using SwagResponseSchema annotation
     *
     * @return void
     */
    private function assignFromAnnotations(): void
    {
        $swagResponses = array_filter($this->annotations, function ($annotation) {
            return $annotation instanceof SwagResponseSchema;
        });

        foreach ($swagResponses as $annotate) {
            $mimeTypes = $annotate->mimeTypes ?? $this->config->getResponseContentTypes();

            foreach ($mimeTypes as $mimeType) {
                $content = (new Content())->setMimeType($mimeType);

                $response = (new Response())->setCode($annotate->statusCode)->setDescription($annotate->description);

                // push basic response since no entity or format was defined
                if (empty($annotate->schemaFormat) && empty($annotate->refEntity)) {
                    $response->pushContent($content);
                    $this->operation->pushResponse($response);
                    continue;
                }

                // push text/plain
                if ($mimeType == 'text/plain') {
                    $schema = (new Schema())->setType('string')->setFormat($annotate->schemaFormat ?? '');
                    $content->setSchema($schema);
                    $response->pushContent($content);
                    $this->operation->pushResponse($response);
                    continue;
                }

                try {
                    $schema = $this->getMimeTypeSchema(
                        $mimeType,
                        $annotate->schemaType,
                        $ref ?? $annotate->refEntity
                    );
                    $content->setSchema(
                        $annotate->schemaFormat ? $schema->setFormat($annotate->schemaFormat) : $schema
                    );
                    $response->pushContent($content);
                    $this->operation->pushResponse($response);
                } catch (\Exception $e) {
                    throw new \RuntimeException(
                        sprintf(
                            'Unable to build response schema for `%s`, error: %s',
                            $this->route->getTemplate(),
                            $e->getMessage()
                        )
                    );
                }
            }
        }
    }

    /**
     * Set response from Crud actions
     *
     * @return void
     */
    private function assignFromCrudActions(): void
    {
        if ($this->operation->hasSuccessResponseCode()) {
            return;
        }

        $action = strtolower($this->route->getAction());
        $crudActions = [
            'index' => 'array',
            'add' => 'object',
            'view' => 'object',
            'edit' => 'object',
        ];

        if (!$this->schema || !array_key_exists($action, $crudActions)) {
            return;
        }

        $response = (new Response())->setCode('200');

        foreach ($this->config->getResponseContentTypes() as $mimeType) {
            $schema = $this->getMimeTypeSchema($mimeType, $crudActions[$action]);

            $response->pushContent(
                (new Content())
                    ->setSchema($schema)
                    ->setMimeType($mimeType)
            );
        }

        $this->operation->pushResponse($response);
    }

    /**
     * Gets a schema based on mimetype
     *
     * @param string $mimeType a mime type (e.g. application/xml, application/json)
     * @param string $schemaType object or array
     * @param string|null $schema the openapi schema $ref or null
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function getMimeTypeSchema(string $mimeType, string $schemaType, ?string $schema = null)
    {
        if (is_null($schema)) {
            $schema = $this->schema instanceof Schema ? $this->schema : new Schema();
        }

        switch ($mimeType) {
            case 'application/xml':
                return (new XmlMedia($schema, $this->swagger))->buildSchema($schemaType);
            case 'application/hal+json':
            case 'application/vnd.hal+json':
                return (new HalJson($schema, $this->swagger))->buildSchema($schemaType);
            case 'application/ld+json':
                return (new JsonLd($schema, $this->swagger))->buildSchema($schemaType);
            case 'text/plain':
                return (new Schema())->setType('string');
        }

        return (new Generic($schema, $this->swagger))->buildSchema($schemaType);
    }

    /**
     * Assigns a default responses
     *
     * delete: 204 with empty response body
     * default: 200 with empty response body and first element from responseContentTypes config as mimeType
     *
     * @return void
     */
    private function assignDefaultResponses(): void
    {
        if ($this->operation->hasSuccessResponseCode()) {
            return;
        }

        if (strtolower($this->route->getAction()) == 'delete') {
            $this->operation->pushResponse(
                (new Response())
                    ->setCode('204')
                    ->setDescription('Resource deleted')
            );

            return;
        }

        $response = (new Response())->setCode('200');

        if (in_array($this->operation->getHttpMethod(), ['OPTIONS','HEAD'])) {
            $this->operation->pushResponse($response);

            return;
        }

        foreach ($this->config->getResponseContentTypes() as $mimeType) {
            $schema = (new Schema())->setDescription('');

            if ($mimeType == 'application/xml') {
                $schema->setXml((new Xml())->setName('response'));
            }

            $response->pushContent(
                (new Content())->setMimeType($mimeType)->setSchema($schema)
            );
        }

        $this->operation->pushResponse($response);
    }
}
