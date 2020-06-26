<?php

namespace SwaggerBake\Lib\Schema;

use Cake\Validation\ValidationRule;
use Cake\Validation\Validator;
use ReflectionFunction;
use SwaggerBake\Lib\Decorator\PropertyDecorator;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

/**
 * Class SchemaPropertyValidation
 * @package SwaggerBake\Lib\Schema
 *
 * Checks validation rules in your projects Table classes and set Schema Properties from them
 */
class SchemaPropertyValidation
{
    /** @var Validator  */
    private $validator;

    /** @var SchemaProperty  */
    private $schemaProperty;

    /** @var string */
    private $propertyName;

    public function __construct
    (
        Validator $validator,
        SchemaProperty $schemaProperty,
        PropertyDecorator $propertyDecorator
    )
    {
        $this->validator = $validator;
        $this->schemaProperty = $schemaProperty;
        $this->propertyName = $propertyDecorator->getName();
    }

    /**
     * Sets SchemaProperty properties from Cake validation rules and returns an instance of SchemaProperty
     *
     * @return SchemaProperty
     */
    public function withValidations() : SchemaProperty
    {
        $this
            ->defineRequired()
            ->defineMaximum()
            ->defineExclusiveMaximum()
            ->defineMaxLength()
            ->defineMinimum()
            ->defineExclusiveMinimum()
            ->defineMinLength()
            ->definePattern()
            ->defineEnum()
            ->defineMinItems()
            ->defineMaxItems()
        ;

        return $this->schemaProperty;
    }

    /**
     * Returns an instance of ValidationRule for the given $ruleName if it exists, null otherwise
     *
     * @param string $ruleName
     * @return ValidationRule|null
     */
    private function getValidationRule(string $ruleName): ?ValidationRule
    {
        $validationSet = $this->validator->field($this->propertyName);
        return $validationSet->rule($ruleName);
    }

    /**
     * Returns a mixed variable for the validation rules condition if the rule exists, null otherwise
     *
     * @param string $ruleName
     * @return array|mixed|null
     */
    private function getValidationRuleValue(string $ruleName)
    {
        $validationRule = $this->getValidationRule($ruleName);
        if (is_null($validationRule)) {
            return null;
        }

        $result = $validationRule->get('pass');

        if (!is_array($result) || empty($result)) {
            return null;
        }

        return $result;
    }

    /**
     * Returns a mixed variable for the validation rules condition if the rule exists, null otherwise. This is for
     * ValidationRule's which use closures.
     *
     * @param string $rule
     * @return $this
     */
    private function getValidationRuleValueFromClosure(string $rule)
    {
        $validationRule = $this->getValidationRule($rule);
        if (!$validationRule instanceof ValidationRule) {
            return null;
        }

        $result = $validationRule->get('rule');
        try {
            $vars = (new ReflectionFunction($result))->getStaticVariables();
        } catch(\Exception $e) {
            return null;
        }

        if (empty($vars) || !isset($vars['count'])) {
            return null;
        }

        return $vars['count'];
    }

    /**
     * @return SchemaPropertyValidation
     */
    private function defineMaxLength() : SchemaPropertyValidation
    {
        $result = $this->getValidationRuleValue('maxLength');
        if (!empty($result)) {
            $this->schemaProperty->setMaxLength(intval(reset($result)));
        }
        return $this;
    }

    /**
     * @return SchemaPropertyValidation
     */
    private function defineRequired() : SchemaPropertyValidation
    {
        $validationSet = $this->validator->field($this->propertyName);
        $isPresenceRequired = $validationSet->isPresenceRequired();

        if ($isPresenceRequired === true) {
            $this->schemaProperty->setRequired(true);
        } else if (is_string($isPresenceRequired)) {
            switch (strtoupper($isPresenceRequired))
            {
                case 'UPDATE':
                    $this->schemaProperty->setRequirePresenceOnUpdate(true);
                    break;
                case 'CREATE':
                    $this->schemaProperty->setRequirePresenceOnCreate(true);
                    break;
            }
        }

        return $this;
    }

    /**
     * @return SchemaPropertyValidation
     */
    private function defineMinLength() : SchemaPropertyValidation
    {
        $result = $this->getValidationRuleValue('minLength');
        if (!empty($result)) {
            $this->schemaProperty->setMinLength(intval(reset($result)));
        }
        return $this;
    }

    /**
     * @return SchemaPropertyValidation
     */
    private function defineMinimum() : SchemaPropertyValidation
    {
        $result = $this->getValidationRuleValue('greaterThanOrEqual');
        if (!empty($result)) {
            $this->schemaProperty->setMinimum(floatval(end($result)));
        }
        return $this;
    }

    /**
     * @return SchemaPropertyValidation
     */
    private function defineExclusiveMinimum() : SchemaPropertyValidation
    {
        $result = $this->getValidationRuleValue('greaterThan');
        if (!empty($result)) {
            $this->schemaProperty
                ->setMinimum(floatval(end($result)))
                ->setExclusiveMinimum(true);
        }
        return $this;
    }

    /**
     * @return SchemaPropertyValidation
     */
    private function defineMaximum() : SchemaPropertyValidation
    {
        $result = $this->getValidationRuleValue('lessThanOrEqual');
        if (!empty($result)) {
            $this->schemaProperty->setMaximum(floatval(end($result)));
        }
        return $this;
    }

    /**
     * @return SchemaPropertyValidation
     */
    private function defineExclusiveMaximum() : SchemaPropertyValidation
    {
        $result = $this->getValidationRuleValue('lessThan');
        if (!empty($result)) {
            $this->schemaProperty
                ->setMaximum(floatval(end($result)))
                ->setExclusiveMaximum(true);
        }
        return $this;
    }

    /**
     * @return SchemaPropertyValidation
     */
    private function definePattern() : SchemaPropertyValidation
    {
        $result = $this->getValidationRuleValue('regex');
        if (!empty($result)) {
            $this->schemaProperty->setPattern(reset($result));
        }
        return $this;
    }

    /**
     * @return SchemaPropertyValidation
     */
    private function defineEnum() : SchemaPropertyValidation
    {
        $result = $this->getValidationRuleValue('inList');
        if (empty($result)) {
            return $this;
        }

        $items = reset($result);

        if (empty($items)) {
            return $this;
        }

        $this->schemaProperty->setEnum($items);

        return $this;
    }

    /**
     * @return SchemaPropertyValidation
     */
    private function defineMinItems() : SchemaPropertyValidation
    {
        $result = $this->getValidationRuleValueFromClosure('hasAtLeast');
        if (is_numeric($result)) {
            $this->schemaProperty->setMinItems($result);
        }
        return $this;
    }

    /**
     * @return SchemaPropertyValidation
     */
    private function defineMaxItems() : SchemaPropertyValidation
    {
        $result = $this->getValidationRuleValueFromClosure('hasAtMost');
        if (is_numeric($result)) {
            $this->schemaProperty->setMaxItems($result);
        }
        return $this;
    }
}