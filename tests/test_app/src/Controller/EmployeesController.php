<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Controller;

use SwaggerBake\Lib\Annotation as Swag;

/**
 * Employees Controller
 *
 * @property \App\Model\Table\EmployeesTable $Employees
 *
 * @method \App\Model\Entity\Employee[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class EmployeesController extends AppController
{
    /**
     * Gets Employees
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $employees = $this->paginate($this->Employees);

        $this->set(compact('employees'));
        $this->viewBuilder()->setOption('serialize', ['employees']);
    }

    /**
     * View method
     *
     * @param string|null $id Employee id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $employee = $this->Employees->get($id, [
            'contain' => ['DepartmentEmployees', 'EmployeeSalaries', 'EmployeeTitles'],
        ]);

        $this->set('employee', $employee);
        $this->viewBuilder()->setOption('serialize', ['employee']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $employee = $this->Employees->newEmptyEntity();
        if ($this->request->is('post')) {
            $employee = $this->Employees->patchEntity($employee, $this->request->getData());
            if ($this->Employees->save($employee)) {
                $this->Flash->success(__('The employee has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The employee could not be saved. Please, try again.'));
        }
        $this->set(compact('employee'));
        $this->viewBuilder()->setOption('serialize', ['employee']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Employee id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $employee = $this->Employees->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $employee = $this->Employees->patchEntity($employee, $this->request->getData());
            if ($this->Employees->save($employee)) {
                $this->Flash->success(__('The employee has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The employee could not be saved. Please, try again.'));
        }
        $this->set(compact('employee'));
        $this->viewBuilder()->setOption('serialize', ['employee']);
    }

    /**
     * custom-get summary
     *
     * @Swag\SwagPaginator
     * @Swag\SwagQuery(name="queryParamName", type="string", required=false)
     * @Swag\SwagHeader(name="X-HEAD-ATTRIBUTE", type="string", required=false)
     * @Swag\SwagSecurity(name="BearerAuth")
     * @Swag\SwagResponseSchema(refEntity="", description="hello world", httpCode=200)
     * @throws BadRequestException
     * @throws UnauthorizedException
     * @throws ForbiddenException
     * @throws Exception
     */
    public function customGet()
    {
        $hello = 'world';
        $this->set(compact('hello'));
        $this->viewBuilder()->setOption('serialize', ['hello']);
    }

    /**
     * custom-post summary
     *
     * @Swag\SwagRequestBody(description="Hello", ignoreCakeSchema=true)
     * @Swag\SwagForm(name="fieldName", type="string", required=false)
     */
    public function customPost()
    {
        $hello = 'world';
        $this->set(compact('hello'));
        $this->viewBuilder()->setOption('serialize', ['hello']);
    }

    /**
     * custom-hidden should be hidden from swagger
     *
     * @Swag\SwagOperation(isVisible=false)
     */
    public function customHidden()
    {
        $hello = 'world';
        $this->set(compact('hello'));
        $this->viewBuilder()->setOption('serialize', ['hello']);
    }

    /**
     * @Swag\SwagRequestBodyContent(refEntity="", mimeType="text/plain")
     */
    public function customRequestBodyContent()
    {
        $hello = 'world';
        $this->set(compact('hello'));
        $this->viewBuilder()->setOption('serialize', ['hello']);
    }

    /**
     * @Swag\SwagResponseSchema(refEntity="#/components/schemas/Pet", mimeType="application/json")
     * @Swag\SwagResponseSchema(refEntity="", description="fatal error", httpCode=500)
     */
    public function customResponseSchema()
    {
        $hello = 'world';
        $this->set(compact('hello'));
        $this->viewBuilder()->setOption('serialize', ['hello']);
    }

    /**
     * @Swag\SwagDto(class="\SwaggerBakeTest\App\Dto\EmployeeData")
     */
    public function dtoQuery()
    {
        $hello = 'world';
        $this->set(compact('hello'));
        $this->viewBuilder()->setOption('serialize', ['hello']);
    }

    /**
     * @Swag\SwagDto(class="\SwaggerBakeTest\App\Dto\EmployeeData")
     */
    public function dtoPost()
    {
        $hello = 'world';
        $this->set(compact('hello'));
        $this->viewBuilder()->setOption('serialize', ['hello']);
    }
}
