<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use Cake\Form\Form;
use ReflectionClass;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Attribute\OpenApiQueryParam;
use SwaggerBake\Lib\Attribute\OpenApiSchemaProperty;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Schema\SchemaPropertyValidation;
use SwaggerBake\Lib\Utility\DataTypeConversion;

/**
 * Parses the ReflectionClass (or FQN into ReflectionClass) and builds a Schema instance with instances of
 * SchemaProperty. This is used by DTO attributes and Response attributes.
 */
class DtoParser
{
    private ReflectionClass $reflection;

    /**
     * @param \ReflectionClass|string $reflection ReflectionClass instance or the fully qualified namespace of the DTO
     * to be converted into a ReflectionClass instance.
     */
    public function __construct(ReflectionClass|string $reflection)
    {
        if (is_string($reflection) && class_exists($reflection)) {
            $this->reflection = new ReflectionClass($reflection);
        } elseif ($reflection instanceof ReflectionClass) {
            $this->reflection = $reflection;
        }
    }

    /**
     * Returns an array of Parameter instances for use in Query Parameters
     *
     * @return array<\SwaggerBake\Lib\OpenApi\Parameter>
     * @throws \ReflectionException
     */
    public function getParameters(): array
    {
        $parameters = $this->getModellessFormQueryParams();
        foreach ($this->reflection->getProperties() as $reflectionProperty) {
            $queryParam = (new AttributeFactory(
                $reflectionProperty,
                OpenApiQueryParam::class
            ))->createOneOrNull();

            if ($queryParam instanceof OpenApiQueryParam) {
                $parameters[$queryParam->name] = $queryParam->createParameter();
            }
        }

        return array_values($parameters);
    }

    /**
     * Returns an array of SchemaProperty instances for use in Body Requests or Responses.
     *
     * @return array<\SwaggerBake\Lib\OpenApi\SchemaProperty>
     * @throws \ReflectionException
     */
    public function getSchemaProperties(): array
    {
        $schemaProperties = $this->getModellessFormSchemaProperties();
        foreach ($this->reflection->getProperties() as $reflectionProperty) {
            $schemaProperty = (new AttributeFactory(
                $reflectionProperty,
                OpenApiSchemaProperty::class
            ))->createOneOrNull();

            if ($schemaProperty instanceof OpenApiSchemaProperty) {
                $schemaProperties[$schemaProperty->name] = $schemaProperty->create();
            }
        }

        return array_values($schemaProperties);
    }

    /**
     * @link https://book.cakephp.org/4/en/core-libraries/form.html
     * @return array<\SwaggerBake\Lib\OpenApi\SchemaProperty>
     * @throws \ReflectionException
     */
    private function getModellessFormSchemaProperties(): array
    {
        $schemaProperties = [];
        if (!$this->reflection->isSubclassOf(Form::class)) {
            return $schemaProperties;
        }

        /** @var \Cake\Form\Form $form */
        $form = $this->reflection->newInstanceWithoutConstructor();
        $schema = $form->getSchema();
        $validator = $form->getValidator();

        foreach ($schema->fields() as $name) {
            $attr = $schema->field($name);
            $schemaProperty = new SchemaProperty(
                name: $name,
                type: DataTypeConversion::toType($attr['type'])
            );
            $schemaProperty = (new SchemaPropertyValidation($validator, $schemaProperty, $name))->withValidations();
            $schemaProperties[$name] = $schemaProperty;
        }

        return $schemaProperties;
    }

    /**
     * @link https://book.cakephp.org/4/en/core-libraries/form.html
     * @return array<\SwaggerBake\Lib\OpenApi\Parameter>
     * @throws \ReflectionException
     */
    private function getModellessFormQueryParams(): array
    {
        $parameters = [];
        if (!$this->reflection->isSubclassOf(Form::class)) {
            return $parameters;
        }

        /** @var \Cake\Form\Form $form */
        $form = $this->reflection->newInstanceWithoutConstructor();
        $schema = $form->getSchema();

        foreach ($schema->fields() as $name) {
            $attr = $schema->field($name);
            $parameters[$name] = new Parameter(
                in: 'query',
                name: $name,
                schema: (new Schema())->setType(DataTypeConversion::toType($attr['type'])),
            );
        }

        return $parameters;
    }
}
