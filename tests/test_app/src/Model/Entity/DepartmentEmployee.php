<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Model\Entity;

use Cake\ORM\Entity;
use SwaggerBake\Lib\Attribute\OpenApiSchema;

/**
 * DepartmentEmployee Entity
 * @property int $employee_id
 * @property int $department_id
 * @property \Cake\I18n\FrozenDate $from_date
 * @property \Cake\I18n\FrozenDate $to_date
 * @property int $id
 *
 * @property \SwaggerBake\Test\Model\Entity\Employee $employee
 * @property \SwaggerBake\Test\Model\Entity\Department $department
 */
#[OpenApiSchema(visibility: OpenApiSchema::VISIBLE_HIDDEN)]
class DepartmentEmployee extends Entity
{
    /**
     * @inheritDoc
     */
    protected array $_accessible = [
        'employee_id' => true,
        'department_id' => true,
        'from_date' => true,
        'to_date' => true,
        'employee' => true,
        'department' => true,
    ];
}
