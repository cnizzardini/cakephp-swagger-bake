<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use ReflectionClass;
use ReflectionMethod;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Attribute\OpenApiResponse;
use SwaggerBake\Lib\Attribute\OpenApiSchema;
use SwaggerBake\Lib\Attribute\OpenApiSchemaProperty;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\MediaType\Generic;
use SwaggerBake\Lib\MediaType\HalJson;
use SwaggerBake\Lib\MediaType\JsonLd;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\CustomSchemaInterface;
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
    /**
     * @param \SwaggerBake\Lib\Swagger $swagger Swagger
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
     * @param \SwaggerBake\Lib\OpenApi\Schema|null $schema Schema or null
     * @param \ReflectionMethod|null $refMethod ReflectionMethod of the controller action or null
     */
    public function __construct(
        private Swagger $swagger,
        private Configuration $config,
        private Operation $operation,
        private RouteDecorator $route,
        private ?Schema $schema = null,
        private ?ReflectionMethod $refMethod = null
    ) {
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

                if ($this->addResponseRef($response, $mimeType, $openApiResponse)) {
                    continue;
                }

                if ($this->addResponseSchema($response, $mimeType, $openApiResponse)) {
                    continue;
                }

                if ($this->addAssociatedSchema($response, $mimeType, $openApiResponse)) {
                    continue;
                }

                if ($this->addPlainText($response, $mimeType, $openApiResponse)) {
                    continue;
                }

                if ($this->addControllerSchema($response, $mimeType)) {
                    continue;
                }

                $response->pushContent(new Content($mimeType, ''));
                $this->operation->pushResponse($response);
            }
        }
    }

    /**
     * @param \SwaggerBake\Lib\OpenApi\Response $response Response
     * @param string $mimeType The mime type
     * @param \SwaggerBake\Lib\Attribute\OpenApiResponse $openApiResponse OpenApiResponse attribute
     * @return bool
     */
    private function addResponseRef(Response $response, string $mimeType, OpenApiResponse $openApiResponse): bool
    {
        if ($openApiResponse->ref) {
            $schema = (new Schema())
                ->setAllOf([['$ref' => $openApiResponse->ref]])
                ->setType($openApiResponse->schemaType);

            $response->pushContent(new Content($mimeType, $schema));
            $this->operation->pushResponse($response);

            return true;
        }

        return false;
    }

    /**
     * Parses the value of OpenApiResponse::schema into an OpenAPI response schema.
     *
     * @param \SwaggerBake\Lib\OpenApi\Response $response Response
     * @param string $mimeType The mime type
     * @param \SwaggerBake\Lib\Attribute\OpenApiResponse $openApiResponse OpenApiResponse attribute
     * @return bool
     * @throws \ReflectionException
     */
    private function addResponseSchema(Response $response, string $mimeType, OpenApiResponse $openApiResponse): bool
    {
        if ($openApiResponse->schema) {
            $reflection = new ReflectionClass($openApiResponse->schema);
            // check if the DTO contains the OpenApiSchema attribute
            $openApiSchema = (new AttributeFactory(
                $reflection,
                OpenApiSchema::class
            ))->createOneOrNull();

            if ($reflection->implementsInterface(CustomSchemaInterface::class)) {
                /** @var \SwaggerBake\Lib\OpenApi\Schema $schema */
                $schema = $openApiResponse->schema::getOpenApiSchema();
                if ($openApiSchema instanceof OpenApiSchema) {
                    $schema->setVisibility($openApiSchema->visibility);
                } else {
                    $schema->setVisibility(OpenApiSchema::VISIBILE_NEVER);
                }
            } else {
                // add schema to #/components/schemas ?
                if ($openApiSchema instanceof OpenApiSchema) {
                    $schema = $openApiSchema->createSchema();
                } else {
                    $schema = (new Schema())->setVisibility(OpenApiSchema::VISIBILE_NEVER);
                }
                $schema
                    ->setName($reflection->getShortName())
                    ->setType($openApiResponse->schemaType);
            }

            $schema->setIsCustomSchema(true);

            // class level attributes
            $schemaProperties = (new AttributeFactory(
                $reflection,
                OpenApiSchemaProperty::class
            ))->createMany();
            foreach ($schemaProperties as $schemaProperty) {
                $schema->pushProperty($schemaProperty->create());
            }

            // property level attributes
            foreach ($reflection->getProperties() as $reflectionProperty) {
                $schemaProperty = (new AttributeFactory(
                    $reflectionProperty,
                    OpenApiSchemaProperty::class
                ))->createOneOrNull();

                if ($schemaProperty instanceof OpenApiSchemaProperty) {
                    $schema->pushProperty($schemaProperty->create());
                }
            }

            $response->pushContent(new Content($mimeType, $schema));
            $this->operation->pushResponse($response);

            return true;
        }

        return false;
    }

    /**
     * Adds plain text to the response if mime type is `text/plain` and returns true, otherwise returns false
     *
     * @param \SwaggerBake\Lib\OpenApi\Response $response Response
     * @param string $mimeType The mime type
     * @param \SwaggerBake\Lib\Attribute\OpenApiResponse $openApiResponse OpenApiResponse attribute
     * @return bool
     */
    private function addPlainText(Response $response, string $mimeType, OpenApiResponse $openApiResponse): bool
    {
        if ($mimeType == 'text/plain') {
            $schema = (new Schema())
                ->setType('string')
                ->setFormat($openApiResponse->schemaFormat ?? '');
            $response->pushContent(new Content($mimeType, $schema));
            $this->operation->pushResponse($response);

            return true;
        }

        return false;
    }

    /**
     * Adds associated schema.
     *
     * @param \SwaggerBake\Lib\OpenApi\Response $response Response
     * @param string $mimeType The mime type
     * @param \SwaggerBake\Lib\Attribute\OpenApiResponse $openApiResponse OpenApiResponse attribute
     * @return bool
     * @throws \ReflectionException
     */
    private function addAssociatedSchema(Response $response, string $mimeType, OpenApiResponse $openApiResponse): bool
    {
        if (is_array($openApiResponse->associations)) {
            $assocSchema = (new OperationResponseAssociation($this->swagger, $this->route, $this->schema))
                ->build($openApiResponse);
            $schema = $this->getMimeTypeSchema(
                $mimeType,
                $openApiResponse->schemaType,
                $assocSchema
            );
            $response->pushContent(new Content(
                $mimeType,
                $openApiResponse->schemaFormat ? $schema->setFormat($openApiResponse->schemaFormat) : $schema
            ));
            $this->operation->pushResponse($response);

            return true;
        }

        return false;
    }

    /**
     * Adds $this->schema which is derived from the Controller per Cake conventions.
     *
     * @param \SwaggerBake\Lib\OpenApi\Response $response Response
     * @param string $mimeType The mime type
     * @return bool
     */
    private function addControllerSchema(Response $response, string $mimeType): bool
    {
        if ($this->schema != null) {
            $response->pushContent(new Content($mimeType, $this->schema));
            $this->operation->pushResponse($response);

            return true;
        }

        return false;
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

        $schemaMode = $this->swagger->getSchemaByName($this->schema->getName()) ?? $this->schema;

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
            'text/plain' => (new Schema())->setType('string'),
            default => (new Generic($this->swagger))->buildSchema($schema, $schemaType)
        };
    }

    /**
     * Assigns a default response:
     *
     * HTTP DELETE: 204 with empty response body
     * DEFAULT: 200 with empty response body and first element from responseContentTypes config as mimeType
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
