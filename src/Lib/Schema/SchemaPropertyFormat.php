<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Schema;

use Cake\Validation\ValidationRule;
use Cake\Validation\Validator;
use MixerApi\Core\Model\ModelProperty;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

/**
 * Assigns SchemaProperty::format using CakePHP Validator
 *
 * @package SwaggerBake\Lib\Schema
 */
class SchemaPropertyFormat
{
    /**
     * @var array CakePHP validation rule => SchemaProperty::Format
     */
    private const MAPPINGS = [
        'creditCard' => 'credit-card',
        'date' => 'date',
        'dateTime' => 'date-time',
        'email' => 'email',
        'ipv4' => 'ipv4',
        'ipv6' => 'ipv6',
        'latitude' => 'latitude',
        'longitude' => 'longitude',
        'time' => 'time',
        'url' => 'url',
        'urlWithProtocol' => 'url',
        'uuid' => 'uuid',
    ];

    private string $propertyName;

    /**
     * @param \Cake\Validation\Validator $validator Validator
     * @param \SwaggerBake\Lib\OpenApi\SchemaProperty $schemaProperty SchemaProperty
     * @param \MixerApi\Core\Model\ModelProperty $property Property
     */
    public function __construct(
        private Validator $validator,
        private SchemaProperty $schemaProperty,
        ModelProperty $property
    ) {
        $this->propertyName = $property->getName();
    }

    /**
     * Sets SchemaProperty::format using CakePHP validator settings and returns SchemaProperty
     *
     * @return \SwaggerBake\Lib\OpenApi\SchemaProperty
     */
    public function withFormat(): SchemaProperty
    {
        if (!empty($this->schemaProperty->getFormat())) {
            return $this->schemaProperty;
        }

        foreach (self::MAPPINGS as $rule => $format) {
            if ($this->hasValidationRuleValue($rule)) {
                $this->schemaProperty->setFormat($format);
            }
        }

        return $this->schemaProperty;
    }

    /**
     * Checks if a rule exists
     *
     * @param string $rule Rule name
     * @return bool
     */
    private function hasValidationRuleValue(string $rule): bool
    {
        $validationRule = $this->validator->field($this->propertyName)->rule($rule);

        if (!$validationRule instanceof ValidationRule) {
            return false;
        }

        if (!empty($validationRule->get('on'))) {
            return false;
        }

        return true;
    }
}
