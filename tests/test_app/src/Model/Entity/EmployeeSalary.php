<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Model\Entity;

use Cake\ORM\Entity;
use SwaggerBake\Lib\Annotation as SwagAnnotation;
use SwaggerBake\Lib\Attribute\OpenApiSchema;

#[OpenApiSchema(visibility: OpenApiSchema::VISIBLE_NEVER)]
class EmployeeSalary extends Entity
{
    /**
     * @inheritDoc
     */
    protected array $_accessible = [
        'employee_id' => true,
        'salary' => true,
        'from_date' => true,
        'to_date' => true,
        'employee' => true,
    ];
}
