<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use Cake\Core\Exception\CakeException;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Swagger;

/**
 * Class ExceptionHandler
 *
 * @package SwaggerBake\Lib\Operation
 */
class ExceptionHandler
{
    private Swagger $swagger;

    private Configuration $config;

    private string $message = 'Application Error';

    private string $code;

    private string $schema;

    /**
     * @param \phpDocumentor\Reflection\DocBlock\Tags\Throws $throw Throws
     * @param \SwaggerBake\Lib\Swagger $swagger Swagger
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     */
    public function __construct(Throws $throw, Swagger $swagger, Configuration $config)
    {
        $this->swagger = $swagger;
        $this->config = $config;

        $httpCode = 500;
        $exceptionClass = $throw->getType()->__toString();
        $message = $throw->getDescription()->getBodyTemplate();

        if (class_exists($exceptionClass)) {
            $instance = new $exceptionClass();
            if ($instance instanceof CakeException && $instance->getCode() > 0) {
                $httpCode = (int)$instance->getCode();
            }
            if (empty($message)) {
                $class = get_class($instance);
                $pieces = explode('\\', $class);
                if (!empty($pieces)) {
                    $message = end($pieces);
                }
            }
        }

        if ($exceptionClass == '\Cake\Datasource\Exception\RecordNotFoundException') {
            $httpCode = 404;
        }

        $this->schema = $this->whichSchema($exceptionClass);
        $this->code = (string)$httpCode;
        $this->message = $message ?? $this->message;
    }

    /**
     * Search in x-swagger-bake/components/app-exceptions for custom exception schema, otherwise return default.
     *
     * @param string $exceptionClass FQN of exception
     * @return string
     */
    private function whichSchema(string $exceptionClass): string
    {
        $array = $this->swagger->getArray();
        if (isset($array['x-swagger-bake']['components']['schemas']['app-exceptions'])) {
            foreach ($array['x-swagger-bake']['components']['schemas']['app-exceptions'] as $name => $exception) {
                if (isset($exception['x-exception-fqn']) && $exception['x-exception-fqn'] === $exceptionClass) {
                    return '#/x-swagger-bake/components/schemas/app-exceptions/' . $name;
                }
            }
        }

        return '#/components/schemas/' . $this->config->getExceptionSchema();
    }

    /**
     * The HTTP status code associated with the exception
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * The error message associated with the exception
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * The OpenAPI schema defining the exception
     *
     * @return string
     */
    public function getSchema(): string
    {
        return $this->schema;
    }
}
