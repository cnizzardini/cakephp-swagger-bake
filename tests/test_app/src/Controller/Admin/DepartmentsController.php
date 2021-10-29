<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Controller\Admin;

use SwaggerBake\Lib\Attribute\OpenApiOperation;
use SwaggerBakeTest\App\Controller\AppController;

/**
 * Departments Controller
 *
 * @property \App\Model\Table\DepartmentsTable $Departments
 *
 * @method \App\Model\Entity\Department[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class DepartmentsController extends AppController
{
    /**
     * Just a test for prefix routing when two controllers share the same short name
     */
    #[OpenApiOperation(summary: "prefix-routing-test")]
    public function index()
    {
        $departments = $this->paginate($this->Departments);

        $this->set(compact('departments'));
        $this->viewBuilder()->setOption('serialize', 'departments');
    }
}
