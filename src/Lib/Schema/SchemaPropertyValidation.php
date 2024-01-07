<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Schema;

use Cake\Validation\ValidationRule;
use Cake\Validation\Validator;
use Exception;
use MixerApi\Core\Model\ModelProperty;
use ReflectionFunction;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

/**
 * Checks validation rules in your projects Table classes and set Schema Properties from them
 *
 * @package SwaggerBake\Lib\Schema
 */
class SchemaPropertyValidation
{
    private string $propertyName;

    /**
     * @param \Cake\Validation\Validator $validator Validator
     * @param \SwaggerBake\Lib\OpenApi\SchemaProperty $schemaProperty SchemaProperty
     * @param \MixerApi\Core\Model\ModelProperty|string $property Property
     */
    public function __construct(
        private Validator $validator,
        private SchemaProperty $schemaProperty,
        ModelProperty|string $property
    ) {
        $this->propertyName = is_string($property) ? $property : $property->getName();
    }

    /**
     * Sets SchemaProperty properties from Cake validation rules and returns an instance of SchemaProperty
     *
     * @return \SwaggerBake\Lib\OpenApi\SchemaProperty
     */
    public function withValidations(): SchemaProperty
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
            ->assignMinLength();

        return $this->schemaProperty;
    }

    /**
     * Returns a mixed variable for the validation rules condition if the rule exists, null otherwise. This method
     * only rules a rule value of the validation is applied to both creates and update.
     *
     * @param string $rule Rule name
     * @return mixed|array|null
     * @codeCoverageIgnore
     */
    private function getValidationRuleValue(string $rule): mixed
    {
        $validationRule = $this->validator->field($this->propertyName)->rule($rule);
        if (!$validationRule instanceof ValidationRule) {
            return null;
        }

        if (!empty($validationRule->get('on'))) {
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
     * @param string $rule Rule name
     * @return mixed
     * @codeCoverageIgnore
     */
    private function getValidationRuleValueFromClosure(string $rule): mixed
    {
        $validationRule = $this->validator->field($this->propertyName)->rule($rule);
        if (!$validationRule instanceof ValidationRule) {
            return null;
        }

        $result = $validationRule->get('rule');
        try {
            $vars = (new ReflectionFunction($result))->getStaticVariables();
        } catch (Exception $e) {
            return null;
        }

        if (empty($vars) || !isset($vars['count'])) {
            return null;
        }

        return $vars['count'];
    }

    /**
     * @see Validator::maxLength()
     * @return $this
     */
    private function defineMaxLength()
    {
        $result = $this->getValidationRuleValue('maxLength');
        if (!empty($result)) {
            $this->schemaProperty->setMaxLength(intval(reset($result)));

            return $this;
        }

        $result = $this->getValidationRuleValue('lengthBetween');
        if (!empty($result)) {
            $this->schemaProperty->setMaxLength(intval(end($result)));

            return $this;
        }

        return $this;
    }

    /**
     * @see Validator::isPresenceRequired()
     * @return $this
     */
    private function defineRequired()
    {
        $validationSet = $this->validator->field($this->propertyName);
        $isPresenceRequired = $validationSet->isPresenceRequired();

        if ($isPresenceRequired === true) {
            $this->schemaProperty->setRequired(true);
            $this->schemaProperty->setRequirePresenceOnUpdate(true);
            $this->schemaProperty->setRequirePresenceOnCreate(true);
        } elseif (is_string($isPresenceRequired)) {
            switch (strtoupper($isPresenceRequired)) {
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
     * @see Validator::minLength()
     * @return $this
     */
    private function defineMinLength()
    {
        $result = $this->getValidationRuleValue('minLength');
        if (!empty($result)) {
            $this->schemaProperty->setMinLength(intval(reset($result)));

            return $this;
        }

        $result = $this->getValidationRuleValue('lengthBetween');
        if (!empty($result)) {
            $this->schemaProperty->setMinLength(intval(reset($result)));

            return $this;
        }

        return $this;
    }

    /**
     * @see Validator::greaterThanOrEqual()
     * @return $this
     */
    private function defineMinimum()
    {
        $result = $this->getValidationRuleValue('greaterThanOrEqual');
        if (!empty($result)) {
            $this->schemaProperty->setMinimum(floatval(end($result)));
        }

        return $this;
    }

    /**
     * Sets minimum and exclusiveMinimum
     *
     * @see Validator::greaterThan()
     * @return $this
     */
    private function defineExclusiveMinimum()
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
     * @see Validator::lessThanOrEqual()
     * @return $this
     */
    private function defineMaximum()
    {
        $result = $this->getValidationRuleValue('lessThanOrEqual');
        if (!empty($result)) {
            $this->schemaProperty->setMaximum(floatval(end($result)));
        }

        return $this;
    }

    /**
     * Sets maximum and exclusiveMaximum
     *
     * @see Validator::lessThan()
     * @return $this
     */
    private function defineExclusiveMaximum()
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
     * @see Validator::regex()
     * @return $this
     */
    private function definePattern()
    {
        $result = $this->getValidationRuleValue('regex');
        if (!empty($result)) {
            $this->schemaProperty->setPattern(reset($result));
        }

        return $this;
    }

    /**
     * @see Validator::inList()
     * @return $this
     */
    private function defineEnum()
    {
        $result = $this->getValidationRuleValue('inList');
        if (empty($result)) {
            return $this;
        }

        $items = reset($result);

        if (!empty($items)) {
            $this->schemaProperty->setEnum($items);
        }

        return $this;
    }

    /**
     * @see Validator::hasAtLeast()
     * @return $this
     */
    private function defineMinItems()
    {
        $result = $this->getValidationRuleValueFromClosure('hasAtLeast');
        if (is_int($result)) {
            $this->schemaProperty->setMinItems($result);
        }

        return $this;
    }

    /**
     * @see Validator::hasAtMost()
     * @return $this
     */
    private function defineMaxItems()
    {
        $result = $this->getValidationRuleValueFromClosure('hasAtMost');
        if (is_int($result)) {
            $this->schemaProperty->setMaxItems($result);
        }

        return $this;
    }

    /**
     * Assigns a minLength of 1 to scalar types which have no min length defined and do not allow empty
     *
     * @see ValidationSet::isEmptyAllowed()
     * @return $this
     */
    private function assignMinLength()
    {
        if ($this->schemaProperty->isTypeScalar() === false) {
            return $this;
        }

        if ($this->schemaProperty->getMinLength() > 0) {
            return $this;
        }

        if ($this->validator->field($this->propertyName)->isEmptyAllowed() === false) {
            $this->schemaProperty->setMinLength(1);
        }

        return $this;
    }
}
