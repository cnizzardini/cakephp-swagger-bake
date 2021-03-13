<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Controller;

use SwaggerBake\Lib\Annotation as Swag;
use SwaggerBake\Lib\Extension\CakeSearch\Annotation\SwagSearch;

/**
 * Employees Controller
 *
 * @Swag\SwagPath(description="description here", summary="summary here")
 * @property \App\Model\Table\EmployeesTable $Employees
 *
 * @method \App\Model\Entity\Employee[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class EmployeesController extends AppController
{
    public function initialize() : void
    {
        parent::initialize();

        $this->loadComponent('Search.Search', [
            'actions' => ['swagSearch'],
        ]);
    }

    /**
     * Gets Employees
     *
     * @Swag\SwagOperation(tagNames={"Employees","CustomTag"})
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
     * @Swag\SwagOperation(showPut=true)
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
     * Delete employee
     *
     * @param $id
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id)
    {

    }

    /**
     * custom-get summary
     *
     * @Swag\SwagPaginator
     * @Swag\SwagQuery(name="queryParamName", type="string", required=false)
     * @Swag\SwagHeader(name="X-HEAD-ATTRIBUTE", type="string", required=false)
     * @Swag\SwagSecurity(name="BearerAuth")
     * @Swag\SwagResponseSchema(refEntity="", description="hello world", httpCode=200)
     * @throws \Cake\Http\Exception\BadRequestException
     * @throws \Cake\Http\Exception\UnauthorizedException
     * @throws \Cake\Http\Exception\ForbiddenException
     * @throws \Exception
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
     * @Swag\SwagResponseSchema(refEntity="#/components/schemas/Pet")
     * @Swag\SwagResponseSchema(refEntity="", description="deprecated httpCode still works", httpCode=400)
     * @Swag\SwagResponseSchema(refEntity="", description="new statusCode", statusCode="404")
     * @Swag\SwagResponseSchema(refEntity="", description="status code range", statusCode="5XX")
     */
    public function customResponseSchema()
    {
        $hello = 'world';
        $this->set(compact('hello'));
        $this->viewBuilder()->setOption('serialize', ['hello']);
    }

    /**
     * @Swag\SwagResponseSchema(schemaItems={"$ref"="#/components/schemas/Pet"})
     */
    public function schemaItems()
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

    /**
     * @SwagSearch(tableClass="\SwaggerBakeTest\App\Model\Table\EmployeesTable")
     */
    public function swagSearch()
    {
        $query = $this->Employees
            ->find('search', [
                'search' => $this->request->getQueryParams(),
                'collection' => 'default'
            ]);
        $employees = $this->paginate($query);

        $this->set(compact('employees'));
        $this->viewBuilder()->setOption('serialize', ['employees']);
    }

    public function noResponsesDefined()
    {
        $response = 'nokay';
        $this->set(compact('response'));
        $this->viewBuilder()->setOption('serialize', ['response']);
    }
}
