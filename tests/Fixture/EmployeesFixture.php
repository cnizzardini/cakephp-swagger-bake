<?php
declare(strict_types=1);

namespace SwaggerBake\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * EmployeesFixture
 */
class EmployeesFixture extends TestFixture
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'first_name' => 'Lorem ipsum ',
                'last_name' => 'Lorem ipsum do',
                'gender' => 'Lorem ipsum dolor sit amet',
                'birth_date' => '2020-04-09',
                'hire_date' => '2020-04-09',
                'write' => 'wo',
                'read' => 'ro',
                'hide' => 'hidden',
            ],
        ];
        parent::init();
    }
}
