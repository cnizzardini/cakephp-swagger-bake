<?php
declare(strict_types=1);

namespace SwaggerBake\Test\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EmployeeSalaries Model
 *
 * @property \SwaggerBake\Test\Model\Table\EmployeesTable&\Cake\ORM\Association\BelongsTo $Employees
 *
 * @method \SwaggerBake\Test\Model\Entity\EmployeeSalary newEmptyEntity()
 * @method \SwaggerBake\Test\Model\Entity\EmployeeSalary newEntity(array $data, array $options = [])
 * @method \SwaggerBake\Test\Model\Entity\EmployeeSalary[] newEntities(array $data, array $options = [])
 * @method \SwaggerBake\Test\Model\Entity\EmployeeSalary get($primaryKey, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\EmployeeSalary findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\EmployeeSalary patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \SwaggerBake\Test\Model\Entity\EmployeeSalary[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \SwaggerBake\Test\Model\Entity\EmployeeSalary|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\EmployeeSalary saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\EmployeeSalary[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\EmployeeSalary[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\EmployeeSalary[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\EmployeeSalary[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EmployeeSalariesTable extends Table
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

        $this->setTable('employee_salaries');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Employees', [
            'foreignKey' => 'employee_id',
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
            ->integer('salary')
            ->requirePresence('salary', 'create')
            ->notEmptyString('salary');

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

        return $rules;
    }
}
