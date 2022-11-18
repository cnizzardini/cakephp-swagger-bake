<?php

declare(strict_types=1);

namespace SwaggerBakeTest\App\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Validation\Validator;
use SwaggerBake\Lib\Attribute\OpenApiSchemaProperty;

#[OpenApiSchemaProperty(name: 'email', format: 'email')]
class TestForm extends Form
{
    protected function _buildSchema(Schema $schema): Schema
    {
        return $schema
            ->addField('name', 'string')
            ->addField('email', ['type' => 'string'])
            ->addField('comments', ['type' => 'text']);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('name')
            ->maxLength('name', 64)
            ->email('email')
            ->notEmptyString('comments');

        return $validator;
    }

    protected function _execute(array $data): bool
    {
        return true;
    }
}
