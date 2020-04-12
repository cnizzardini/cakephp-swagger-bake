<?php
declare(strict_types=1);

namespace SwaggerBake\Test\Model\Entity;

use Cake\ORM\Entity;

/**
 * Department Entity
 *
 * @property int $id
 * @property string $name
 *
 * @property \SwaggerBake\Test\Model\Entity\DepartmentEmployee[] $department_employees
 */
class Department extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'name' => true,
        'department_employees' => true,
    ];
}
