<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Controller;

use SwaggerBake\Lib\Annotation\SwagQuery;
use SwaggerBake\Lib\Annotation\SwagPaginator;

/**
 * EmployeeSalaries Controller
 *
 * @property \App\Model\Table\EmployeeSalariesTable $EmployeeSalaries
 *
 * @method \App\Model\Entity\EmployeeSalary[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class EmployeeSalariesController extends AppController
{
    /**
     * Index method
     * @SwagPaginator
     * @return \Cake\Http\Response|null|void Renders view
     */
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
     * @param string|null $id Employee Salary id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
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
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
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
     *
     * @param string|null $id Employee Salary id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
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
     *
     * @param string|null $id Employee Salary id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
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
