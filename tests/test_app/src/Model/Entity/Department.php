<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Department Entity
 *
 * @property int $id
 * @property string $name this_is_a_unit_test_for_description
 *
 * @property \SwaggerBake\Test\Model\Entity\DepartmentEmployee[] $department_employees
 */
class Department extends Entity
{
    /**
     * @inheritDoc
     */
    protected array $_accessible = [
        'name' => true,
        'department_employees' => true,
    ];
}
