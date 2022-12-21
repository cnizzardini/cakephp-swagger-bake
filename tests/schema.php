<?php
declare(strict_types=1);

return [
    [
        'table' => 'bakers',
        'columns' => [
            'employee_id' => ['type' => 'integer', 'length' => 11, 'default' => null,],
            'title' => ['type' => 'string', 'length' => 50, 'default' => null, ],
            'from_date' => ['type' => 'date', 'length' => null, 'default' => null,],
            'to_date' => ['type' => 'date', 'length' => null, 'null' => true, 'default' => null,],
            'id' => ['type' => 'integer', 'length' => 11, 'default' => null, 'autoIncrement' => true,],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
    ],
    [
        'table' => 'department_employees',
        'columns' => [
            'employee_id' => ['type' => 'integer', 'length' => 11, 'default' => null,],
            'department_id' => ['type' => 'smallinteger', 'length' => 6, 'default' => null,],
            'from_date' => ['type' => 'date', 'length' => null, 'default' => null,],
            'to_date' => ['type' => 'date', 'length' => null, 'default' => null,],
            'id' => ['type' => 'integer', 'length' => 11, 'default' => null, 'autoIncrement' => true,],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
    ],
    [
        'table' => 'departments',
        'columns' => [
            'id' => ['type' => 'smallinteger', 'length' => 6, 'default' => null,],
            'name' => ['type' => 'string', 'length' => 64, 'default' => null, ],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'dept_name' => ['type' => 'unique', 'columns' => ['name'], 'length' => []],
        ],
    ],
    [
        'table' => 'employee_salaries',
        'columns' => [
            'employee_id' => ['type' => 'integer', 'length' => 11, 'default' => null,],
            'salary' => ['type' => 'integer', 'length' => 11, 'default' => null,],
            'from_date' => ['type' => 'date', 'length' => null, 'default' => null,],
            'to_date' => ['type' => 'date', 'length' => null, 'default' => null,],
            'id' => ['type' => 'integer', 'length' => 11, 'default' => null, 'autoIncrement' => true,],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
    ],
    [
        'table' => 'employees',
        'columns' => [
            'id' => ['type' => 'integer', 'length' => 11, 'default' => null,],
            'first_name' => ['type' => 'string', 'length' => 14, 'default' => null, ],
            'last_name' => ['type' => 'string', 'length' => 16, 'default' => null, ],
            'gender' => ['type' => 'string', 'length' => null, 'default' => null, ],
            'hire_date' => ['type' => 'date', 'length' => null, 'default' => null,],
            'birth_date' => ['type' => 'date', 'length' => null, 'default' => null,],
            'write' => ['type' => 'string', 'length' => null, 'default' => null, ],
            'read' => ['type' => 'string', 'length' => null, 'default' => null, ],
            'hide' => ['type' => 'string', 'length' => null, 'default' => null, ],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
    ],
    [
        'table' => 'employee_titles',
        'columns' => [
            'employee_id' => ['type' => 'integer', 'length' => 11, 'default' => null,],
            'title' => ['type' => 'string', 'length' => 50, 'default' => null, ],
            'from_date' => ['type' => 'date', 'length' => null, 'default' => null,],
            'to_date' => ['type' => 'date', 'length' => null, 'null' => true, 'default' => null,],
            'id' => ['type' => 'integer', 'length' => 11, 'default' => null, 'autoIncrement' => true,],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
    ]
];