<?php
declare(strict_types=1);

namespace SwaggerBake\Test\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DepartmentEmployees Model
 *
 * @property \SwaggerBake\Test\Model\Table\EmployeesTable&\Cake\ORM\Association\BelongsTo $Employees
 * @property \SwaggerBake\Test\Model\Table\DepartmentsTable&\Cake\ORM\Association\BelongsTo $Departments
 *
 * @method \SwaggerBake\Test\Model\Entity\DepartmentEmployee newEmptyEntity()
 * @method \SwaggerBake\Test\Model\Entity\DepartmentEmployee newEntity(array $data, array $options = [])
 * @method \SwaggerBake\Test\Model\Entity\DepartmentEmployee[] newEntities(array $data, array $options = [])
 * @method \SwaggerBake\Test\Model\Entity\DepartmentEmployee get($primaryKey, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\DepartmentEmployee findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\DepartmentEmployee patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \SwaggerBake\Test\Model\Entity\DepartmentEmployee[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \SwaggerBake\Test\Model\Entity\DepartmentEmployee|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\DepartmentEmployee saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\DepartmentEmployee[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\DepartmentEmployee[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\DepartmentEmployee[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\DepartmentEmployee[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class DepartmentEmployeesTable extends Table
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

        $this->setTable('department_employees');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Employees', [
            'foreignKey' => 'employee_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Departments', [
            'foreignKey' => 'department_id',
            'joinType' => 'INNER',
        ]);
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
            ->date('from_date')
            ->requirePresence('from_date', 'create')
            ->notEmptyDate('from_date');

        $validator
            ->date('to_date')
            ->requirePresence('to_date', 'create')
            ->notEmptyDate('to_date');

        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

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
        $rules->add($rules->existsIn(['employee_id'], 'Employees'));
        $rules->add($rules->existsIn(['department_id'], 'Departments'));

        return $rules;
    }
}
