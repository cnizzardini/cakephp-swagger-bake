<?php
declare(strict_types=1);

namespace SwaggerBake\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DepartmentEmployeesFixture
 */
class DepartmentEmployeesFixture extends TestFixture
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->records = [
            [
                'employee_id' => 1,
                'department_id' => 1,
                'from_date' => '2020-04-09',
                'to_date' => '2020-04-09',
                'id' => 1,
            ],
        ];
        parent::init();
    }
}
