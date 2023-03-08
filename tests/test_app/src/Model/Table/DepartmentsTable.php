<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Departments Model
 *
 * @property \SwaggerBakeTest\SwaggerBakeTest\App\Model\Table\DepartmentEmployeesTable&\Cake\ORM\Association\HasMany $DepartmentEmployees
 *
 * @method \SwaggerBakeTest\SwaggerBakeTest\App\Model\Entity\Department newEmptyEntity()
 * @method \SwaggerBakeTest\SwaggerBakeTest\App\Model\Entity\Department newEntity(array $data, array $options = [])
 * @method \SwaggerBakeTest\SwaggerBakeTest\App\Model\Entity\Department[] newEntities(array $data, array $options = [])
 * @method \SwaggerBakeTest\SwaggerBakeTest\App\Model\Entity\Department get($primaryKey, $options = [])
 * @method \SwaggerBakeTest\SwaggerBakeTest\App\Model\Entity\Department findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \SwaggerBakeTest\SwaggerBakeTest\App\Model\Entity\Department patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \SwaggerBakeTest\SwaggerBakeTest\App\Model\Entity\Department[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \SwaggerBakeTest\SwaggerBakeTest\App\Model\Entity\Department|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \SwaggerBakeTest\SwaggerBakeTest\App\Model\Entity\Department saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \SwaggerBakeTest\SwaggerBakeTest\App\Model\Entity\Department[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \SwaggerBakeTest\SwaggerBakeTest\App\Model\Entity\Department[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \SwaggerBakeTest\SwaggerBakeTest\App\Model\Entity\Department[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \SwaggerBakeTest\SwaggerBakeTest\App\Model\Entity\Department[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class DepartmentsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('departments');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->hasMany('DepartmentEmployees', [
            'foreignKey' => 'department_id',
        ]);

        $this->addBehavior('Search.Search');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 64)
            ->requirePresence('name', 'create')
            ->notEmptyString('name')
            ->add('name', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['name']));

        return $rules;
    }
}
