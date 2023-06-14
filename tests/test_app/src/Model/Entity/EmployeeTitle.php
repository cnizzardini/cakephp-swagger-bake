<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Model\Entity;

use Cake\ORM\Entity;

/**
 * EmployeeTitle Entity
 *
 * @property int $employee_id
 * @property string $title
 * @property \Cake\I18n\FrozenDate $from_date
 * @property \Cake\I18n\FrozenDate|null $to_date
 * @property int $id
 *
 * @property \App\Model\Entity\Employee $employee
 */
class EmployeeTitle extends Entity
{
    /**
     * @inheritDoc
     */
    protected array $_accessible = [
        'employee_id' => true,
        'title' => true,
        'from_date' => true,
        'to_date' => true,
        'employee' => true,
    ];
}
