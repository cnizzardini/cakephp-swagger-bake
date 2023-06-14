<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Controller;

use SwaggerBake\Lib\Attribute\OpenApiPaginator;

/**
 * EmployeeSalaries Controller
 */
class EmployeeSalariesController extends AppController
{
    public ?string $defaultTable = 'EmployeeSalaries';

    #[OpenApiPaginator]
    public function index()
    {
        $this->paginate = [
            'contain' => ['Employees'],
        ];
        $employeeSalaries = $this->paginate($this->EmployeeSalaries);

        $this->set(compact('employeeSalaries'));
        $this->viewBuilder()->setOption('serialize', ['employeeSalaries']);
    }

    /**
     * View method
     *
     * This is overwritten in swagger-with-existing.yml
     */
    public function view($id = null)
    {
        $employeeSalary = $this->EmployeeSalaries->get($id, [
            'contain' => ['Employees'],
        ]);

        $this->set('employeeSalary', $employeeSalary);
        $this->viewBuilder()->setOption('serialize', ['employeeSalary']);
    }

    /**
     * Add method
     */
    public function add()
    {
        $employeeSalary = $this->EmployeeSalaries->newEmptyEntity();
        if ($this->request->is('post')) {
            $employeeSalary = $this->EmployeeSalaries->patchEntity($employeeSalary, $this->request->getData());
            if ($this->EmployeeSalaries->save($employeeSalary)) {
                $this->Flash->success(__('The employee salary has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The employee salary could not be saved. Please, try again.'));
        }
        $employees = $this->EmployeeSalaries->Employees->find('list', ['limit' => 200]);
        $this->set(compact('employeeSalary', 'employees'));
        $this->viewBuilder()->setOption('serialize', ['employeeSalary']);
    }

    /**
     * Edit method
     */
    public function edit($id = null)
    {
        $employeeSalary = $this->EmployeeSalaries->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $employeeSalary = $this->EmployeeSalaries->patchEntity($employeeSalary, $this->request->getData());
            if ($this->EmployeeSalaries->save($employeeSalary)) {
                $this->Flash->success(__('The employee salary has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The employee salary could not be saved. Please, try again.'));
        }
        $employees = $this->EmployeeSalaries->Employees->find('list', ['limit' => 200]);
        $this->set(compact('employeeSalary', 'employees'));
        $this->viewBuilder()->setOption('serialize', ['employeeSalary']);
    }

    /**
     * Delete method
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $employeeSalary = $this->EmployeeSalaries->get($id);
        if ($this->EmployeeSalaries->delete($employeeSalary)) {
            $this->Flash->success(__('The employee salary has been deleted.'));
        } else {
            $this->Flash->error(__('The employee salary could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
