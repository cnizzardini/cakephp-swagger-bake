<?php
declare(strict_types=1);

namespace SwaggerBake\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * EmployeeSalariesFixture
 */
class EmployeeSalariesFixture extends TestFixture
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->records = [
            [
                'employee_id' => 1,
                'salary' => 1,
                'from_date' => '2020-04-09',
                'to_date' => '2020-04-09',
                'id' => 1,
            ],
        ];
        parent::init();
    }
}
