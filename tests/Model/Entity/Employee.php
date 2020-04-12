<?php
declare(strict_types=1);

namespace SwaggerBake\Test\Model\Entity;

use Cake\ORM\Entity;

/**
 * Employee Entity
 *
 * @property int $id
 * @property \Cake\I18n\FrozenDate $birth_date
 * @property string $first_name
 * @property string $last_name
 * @property string $gender
 * @property \Cake\I18n\FrozenDate $hire_date
 *
 * @property \SwaggerBake\Test\Model\Entity\DepartmentEmployee[] $department_employees
 * @property \SwaggerBake\Test\Model\Entity\EmployeeSalary[] $employee_salaries
 * @property \SwaggerBake\Test\Model\Entity\EmployeeTitle[] $employee_titles
 */
class Employee extends Entity
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
        'birth_date' => true,
        'first_name' => true,
        'last_name' => true,
        'gender' => true,
        'hire_date' => true,
        'department_employees' => true,
        'employee_salaries' => true,
        'employee_titles' => true,
    ];
}
