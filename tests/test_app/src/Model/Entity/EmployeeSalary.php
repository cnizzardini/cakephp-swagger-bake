<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Model\Entity;

use Cake\ORM\Entity;
use SwaggerBake\Lib\Annotation as SwagAnnotation;

/**
 * EmployeeSalary Entity
 *
 * @SwagAnnotation\SwagEntity(isVisible=false)
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
