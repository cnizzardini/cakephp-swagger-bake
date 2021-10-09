<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Controller;

use SwaggerBake\Lib\Attribute\OpenApiPaginator;
use SwaggerBake\Lib\Attribute\OpenApiPath;

#[OpenApiPath(isVisible: false)]
class EmployeeTitlesController extends AppController
{
    /**
     * Index method
     * @SwagPaginator
     * @return \Cake\Http\Response|null|void Renders view
     */
    #[OpenApiPaginator]
    public function index()
    {
        $this->paginate = [
            'contain' => ['Employees'],
        ];
        $employeeTitles = $this->paginate($this->EmployeeTitles);

        $this->set(compact('employeeTitles'));
        $this->viewBuilder()->setOption('serialize', ['employeeTitles']);
    }

    /**
     * View method
     *
     * @param string|null $id Employee Title id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $employeeTitle = $this->EmployeeTitles->get($id, [
            'contain' => ['Employees'],
        ]);

        $this->set('employeeTitle', $employeeTitle);
        $this->viewBuilder()->setOption('serialize', ['employeeTitle']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $employeeTitle = $this->EmployeeTitles->newEmptyEntity();
        if ($this->request->is('post')) {
            $employeeTitle = $this->EmployeeTitles->patchEntity($employeeTitle, $this->request->getData());
            if ($this->EmployeeTitles->save($employeeTitle)) {
                $this->Flash->success(__('The employee title has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The employee title could not be saved. Please, try again.'));
        }
        $employees = $this->EmployeeTitles->Employees->find('list', ['limit' => 200]);
        $this->set(compact('employeeTitle', 'employees'));
        $this->viewBuilder()->setOption('serialize', ['employeeTitle']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Employee Title id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $employeeTitle = $this->EmployeeTitles->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $employeeTitle = $this->EmployeeTitles->patchEntity($employeeTitle, $this->request->getData());
            if ($this->EmployeeTitles->save($employeeTitle)) {
                $this->Flash->success(__('The employee title has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The employee title could not be saved. Please, try again.'));
        }
        $employees = $this->EmployeeTitles->Employees->find('list', ['limit' => 200]);
        $this->set(compact('employeeTitle', 'employees'));
        $this->viewBuilder()->setOption('serialize', ['employeeTitle']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Employee Title id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $employeeTitle = $this->EmployeeTitles->get($id);
        if ($this->EmployeeTitles->delete($employeeTitle)) {
            $this->Flash->success(__('The employee title has been deleted.'));
        } else {
            $this->Flash->error(__('The employee title could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
