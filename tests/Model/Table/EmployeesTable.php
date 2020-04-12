<?php
declare(strict_types=1);

namespace SwaggerBake\Test\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Employees Model
 *
 * @property \SwaggerBake\Test\Model\Table\DepartmentEmployeesTable&\Cake\ORM\Association\HasMany $DepartmentEmployees
 * @property \SwaggerBake\Test\Model\Table\EmployeeSalariesTable&\Cake\ORM\Association\HasMany $EmployeeSalaries
 * @property \SwaggerBake\Test\Model\Table\EmployeeTitlesTable&\Cake\ORM\Association\HasMany $EmployeeTitles
 *
 * @method \SwaggerBake\Test\Model\Entity\Employee newEmptyEntity()
 * @method \SwaggerBake\Test\Model\Entity\Employee newEntity(array $data, array $options = [])
 * @method \SwaggerBake\Test\Model\Entity\Employee[] newEntities(array $data, array $options = [])
 * @method \SwaggerBake\Test\Model\Entity\Employee get($primaryKey, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\Employee findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\Employee patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \SwaggerBake\Test\Model\Entity\Employee[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \SwaggerBake\Test\Model\Entity\Employee|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\Employee saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\Employee[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\Employee[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\Employee[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \SwaggerBake\Test\Model\Entity\Employee[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EmployeesTable extends Table
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

        $this->setTable('employees');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->hasMany('DepartmentEmployees', [
            'foreignKey' => 'employee_id',
        ]);
        $this->hasMany('EmployeeSalaries', [
            'foreignKey' => 'employee_id',
        ]);
        $this->hasMany('EmployeeTitles', [
            'foreignKey' => 'employee_id',
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
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->date('birth_date')
            ->requirePresence('birth_date', 'create')
            ->notEmptyDate('birth_date');

        $validator
            ->scalar('first_name')
            ->maxLength('first_name', 14)
            ->requirePresence('first_name', 'create')
            ->notEmptyString('first_name');

        $validator
            ->scalar('last_name')
            ->maxLength('last_name', 16)
            ->requirePresence('last_name', 'create')
            ->notEmptyString('last_name');

        $validator
            ->scalar('gender')
            ->requirePresence('gender', 'create')
            ->notEmptyString('gender');

        $validator
            ->date('hire_date')
            ->requirePresence('hire_date', 'create')
            ->notEmptyDate('hire_date');

        return $validator;
    }
}
