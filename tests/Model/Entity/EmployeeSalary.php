<?php
declare(strict_types=1);

namespace SwaggerBake\Test\Model\Entity;

use Cake\ORM\Entity;

/**
 * EmployeeSalary Entity
 *
 * @property int $employee_id
 * @property int $salary
 * @property \Cake\I18n\FrozenDate $from_date
 * @property \Cake\I18n\FrozenDate $to_date
 * @property int $id
 *
 * @property \SwaggerBake\Test\Model\Entity\Employee $employee
 */
class EmployeeSalary extends Entity
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
        'employee_id' => true,
        'salary' => true,
        'from_date' => true,
        'to_date' => true,
        'employee' => true,
    ];
}
