<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use ReflectionMethod;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Attribute\OpenApiResponse;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\MediaType\Generic;
use SwaggerBake\Lib\MediaType\HalJson;
use SwaggerBake\Lib\MediaType\JsonLd;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Response;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\Xml as OpenApiXml;
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Swagger;

/**
 * Builds OpenAPI Operation Responses for CRUD actions and controller actions annotated with SwagResponseSchema
 *
 * @internal
 */
class OperationResponse
{
    private Swagger $swagger;

    private Configuration $config;

    private Operation $operation;

    private ?ReflectionMethod $refMethod;

    private RouteDecorator $route;

    /**
     * @var \SwaggerBake\Lib\OpenApi\Schema|null
     */
    private $schema;

    /**
     * @param \SwaggerBake\Lib\Swagger $swagger Swagger
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
     * @param \SwaggerBake\Lib\OpenApi\Schema $schema Schema or null
     * @param \ReflectionMethod $refMethod ReflectionMethod or null
     */
    public function __construct(
        Swagger $swagger,
        Configuration $config,
        Operation $operation,
        RouteDecorator $route,
        ?Schema $schema = null,
        ?ReflectionMethod $refMethod = null
    ) {
        $this->swagger = $swagger;
        $this->config = $config;
        $this->operation = $operation;
        $this->refMethod = $refMethod;
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
        $this->assignFromAttributes();
        $this->assignFromCrudActions();
        $this->assignDefaultResponses();

        return $this->operation;
    }

    /**
     * @throws \ReflectionException
     * @return void
     */
    private function assignFromAttributes(): void
    {
        if (!$this->refMethod instanceof ReflectionMethod) {
            return;
        }

        /** @var \SwaggerBake\Lib\Attribute\OpenApiResponse[] $openApiResponses */
        $openApiResponses = (new AttributeFactory($this->refMethod, OpenApiResponse::class))->createMany();

        foreach ($openApiResponses as $openApiResponse) {
            $mimeTypes = $openApiResponse->mimeTypes ?? $this->config->getResponseContentTypes();

            foreach ($mimeTypes as $mimeType) {
                $response = new Response($openApiResponse->statusCode, $openApiResponse->description);

                // push text/plain response and the continue to next mime/type
                if ($mimeType == 'text/plain') {
                    $schema = (new Schema())->setType('string')->setFormat($openApiResponse->schemaFormat ?? '');
                    $response->pushContent(new Content($mimeType, $schema));
                    $this->operation->pushResponse($response);
                    continue;
                }

                // push basic response since no entity or format was defined and continue to next mime/type
                if (empty($openApiResponse->ref) && empty($openApiResponse->associations)) {
                    $response->pushContent(new Content($mimeType, ''));
                    $this->operation->pushResponse($response);
                    continue;
                }

                $assocSchema = null;
                if (is_array($openApiResponse->associations)) {
                    $assocSchema = (new OperationResponseAssociation($this->swagger, $this->route, $this->schema))
                        ->build($openApiResponse);
                }

                $schema = $this->getMimeTypeSchema(
                    $mimeType,
                    $openApiResponse->schemaType,
                    $assocSchema ?? $openApiResponse->ref
                );

                $response->pushContent(new Content(
                    $mimeType,
                    $openApiResponse->schemaFormat ? $schema->setFormat($openApiResponse->schemaFormat) : $schema
                ));
                $this->operation->pushResponse($response);
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
        if ($this->operation->hasSuccessResponseCode() || !$this->schema) {
            return;
        }

        $action = strtolower($this->route->getAction());
        $actionTypes = [
            'index' => 'array',
            'add' => 'object',
            'view' => 'object',
            'edit' => 'object',
        ];

        if (!array_key_exists($action, $actionTypes)) {
            return;
        }

        $schemaType = $actionTypes[$action];

        $schemaMode = $this->swagger->getSchemaByName($this->schema->getName() . '-Read') ?? $this->schema;

        $response = new Response('200');

        foreach ($this->config->getResponseContentTypes() as $mimeType) {
            $schema = $this->getMimeTypeSchema($mimeType, $schemaType, $schemaMode->getRefPath());
            $response->pushContent(new Content($mimeType, $schema));
        }

        $this->operation->pushResponse($response);
    }

    /**
     * Gets a schema based on mimetype
     *
     * @param string $mimeType a mime type (e.g. application/xml, application/json)
     * @param string $schemaType object or array
     * @param \SwaggerBake\Lib\OpenApi\Schema|string $schema Schema or an OpenApi $ref string
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function getMimeTypeSchema(string $mimeType, string $schemaType, Schema|string $schema): Schema
    {
        return match ($mimeType) {
            'application/xml' => (new Generic($this->swagger))
                ->buildSchema($schema, $schemaType)
                ->setXml((new OpenApiXml())->setName('response')),
            'application/hal+json','application/vnd.hal+json' => (new HalJson())->buildSchema($schema, $schemaType),
            'application/ld+json' => (new JsonLd())->buildSchema($schema, $schemaType),
            'application/text/plain' => (new Schema())->setType('string'),
            default => (new Generic($this->swagger))->buildSchema($schema, $schemaType)
        };
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
            $this->operation->pushResponse(new Response('204', 'Resource deleted'));

            return;
        }

        $response = new Response('200');

        if (in_array($this->operation->getHttpMethod(), ['OPTIONS','HEAD'])) {
            $this->operation->pushResponse($response);

            return;
        }

        foreach ($this->config->getResponseContentTypes() as $mimeType) {
            $schema = (new Schema())->setDescription('');

            if ($mimeType == 'application/xml') {
                $schema->setXml((new OpenApiXml())->setName('response'));
            }

            $response->pushContent(new Content($mimeType, $schema));
        }

        $this->operation->pushResponse($response);
    }
}
