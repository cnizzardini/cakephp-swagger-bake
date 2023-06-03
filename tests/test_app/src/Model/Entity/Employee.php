<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Model\Entity;

use Cake\ORM\Entity;
use SwaggerBake\Lib\Attribute\OpenApiSchemaProperty;

#[OpenApiSchemaProperty(name: 'gender', example: 'female', enum: ['male','female','other'])]
#[OpenApiSchemaProperty(name: 'last_name', minLength: 3, maxLength: 59, pattern: '/\W/')]
class Employee extends Entity
{
    /**
     * @inheritDoc
     */
    protected array $_accessible = [
        'first_name' => true,
        'last_name' => true,
        'gender' => true,
        'hire_date' => true,
        'birth_date' => true,
        'write' => true,
        '*' => false
    ];

    /**
     * @inheritDoc
     */
    protected array $_hidden = [
        'hide', 'write'
    ];
}
