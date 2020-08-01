<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Controller;

use SwaggerBake\Lib\Annotation as Swag;

/**
 * Bakers Controller
 *
 * @method \SwaggerBakeTest\App\Model\Entity\Baker[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class BakersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\MethodNotAllowedException When invalid method
     * @Swag\SwagPaginator()
     */
    public function index()
    {
        $this->request->allowMethod('get');
        $bakers = $this->paginate($this->Bakers);

        $this->set(compact('bakers'));
        $this->viewBuilder()->setOption('serialize', 'bakers');
    }

    /**
     * View method
     *
     * @param string|null $id Baker id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @throws \Cake\Datasource\Exception\MethodNotAllowedException When invalid method
     */
    public function view($id = null)
    {
        $this->request->allowMethod('get');

        $baker = $this->Bakers->get($id, [
            'contain' => [],
        ]);

        $this->set('baker', $baker);
        $this->viewBuilder()->setOption('serialize', 'baker');
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void HTTP 200 on successful add
     * @throws \Cake\Datasource\Exception\MethodNotAllowedException When invalid method
     * @throws \Exception
     */
    public function add()
    {
        $this->request->allowMethod('post');
        $baker = $this->Bakers->newEmptyEntity();
        $baker = $this->Bakers->patchEntity($baker, $this->request->getData());
        if ($this->Bakers->save($baker)) {
            $this->viewBuilder()->setOption('serialize', 'baker');
            return;
        }
        throw new \Exception("Record not created");
    }

    /**
     * Edit method
     *
     * @param string|null $id Baker id.
     * @return \Cake\Http\Response|null|void HTTP 200 on successful edit
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @throws \Cake\Datasource\Exception\MethodNotAllowedException When invalid method
     * @throws \Exception
     */
    public function edit($id = null)
    {
        $this->request->allowMethod(['patch', 'post', 'put']);
        $baker = $this->Bakers->get($id, [
            'contain' => [],
        ]);
        $baker = $this->Bakers->patchEntity($baker, $this->request->getData());
        if ($this->Bakers->save($baker)) {
            $this->viewBuilder()->setOption('serialize', 'baker');
            return;
        }
        throw new \Exception("Record not saved");
    }

    /**
     * Delete method
     *
     * @param string|null $id Baker id.
     * @return \Cake\Http\Response|null|void HTTP 204 on success
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @throws \Cake\Datasource\Exception\MethodNotAllowedException When invalid method
     * @throws \Exception
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['delete']);
        $baker = $this->Bakers->get($id);
        if ($this->Bakers->delete($baker)) {
            return $this->response->withStatus(204);
        }
        throw new \Exception("Record not deleted");
    }
}
