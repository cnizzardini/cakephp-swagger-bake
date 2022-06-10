<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use Cake\Core\Exception\CakeException;
use Exception;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use ReflectionClass;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApiExceptionSchemaInterface;
use Throwable;

/**
 * Defines an error responses.
 */
class ExceptionResponse
{
    private ?string $description = null;

    private string $code = '';

    private Schema|string|null $schema = null;

    /**
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     */
    public function __construct(private Configuration $config)
    {
    }

    /**
     * Attempts finding exception Schema or OpenAPI $ref based on the FQN of `@throws` in the following order:
     *
     * 1. Checks for exceptions implementing OpenApiExceptionSchemaInterface.
     * 2. Checks for CakePHP exceptions.
     * ~~3. Searches in x-swagger-bake/components/app-exceptions for custom exception schema (deprecated).~~
     * 4. Uses the default exception as defined in your swagger_bake config.
     *
     * If an implementation is found, the code, description, and schema will be defined from it.
     *
     * @see \SwaggerBake\Lib\OpenApiExceptionSchemaInterface
     * @param \phpDocumentor\Reflection\DocBlock\Tags\Throws $throw The exception to be thrown
     * @return $this
     */
    public function build(Throws $throw)
    {
        $exceptionFqn = $throw->getType()->__toString();

        try {
            if (!class_exists($exceptionFqn)) {
                throw new Exception("Class $exceptionFqn does not exist");
            }
            $reflection = new ReflectionClass($exceptionFqn);
            if ($reflection->implementsInterface(OpenApiExceptionSchemaInterface::class)) {
                $this->code = $exceptionFqn::getExceptionCode();
                $this->description = $exceptionFqn::getExceptionDescription();
                $this->schema = $exceptionFqn::getExceptionSchema();

                return $this;
            }
        } catch (Throwable $e) {
            $reflection = null;
        }

        $httpCode = null;
        $description = $throw->getDescription()->getBodyTemplate();

        if (class_exists($exceptionFqn)) {
            $instance = new $exceptionFqn();
            if ($instance instanceof CakeException && $instance->getCode() > 0) {
                $httpCode = (string)$instance->getCode();
            }
            if (empty($description) && $reflection) {
                $description = $reflection->getShortName();
            }
        }

        if ($exceptionFqn == '\Cake\Datasource\Exception\RecordNotFoundException') {
            $httpCode = '404';
        }

        $this->code = $httpCode ?? '500';
        $this->description = $description;
        $this->schema = $this->fallback($exceptionFqn);

        return $this;
    }

    /**
     * @deprecated this method may be removed in version 3.
     * @param string $exceptionFqn The FQN of the exception class.
     * @return string|null
     */
    private function fallback(string $exceptionFqn): string|null
    {
        if (empty($this->config->getExceptionSchema())) {
            return null;
        }

        return '#/components/schemas/' . $this->config->getExceptionSchema();
    }

    /**
     * The HTTP status code associated with the exception.
     *
     * @link https://spec.openapis.org/oas/v3.0.3#responses-object
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * The response description associated with the exception.
     *
     * @link https://spec.openapis.org/oas/v3.0.3#responses-object
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * The OpenAPI Schema or $ref string defining the exception.
     *
     * @see \SwaggerBake\Lib\OpenApi\Schema
     * @return \SwaggerBake\Lib\OpenApi\Schema|string|null
     */
    public function getSchema(): Schema|string|null
    {
        return $this->schema;
    }
}
