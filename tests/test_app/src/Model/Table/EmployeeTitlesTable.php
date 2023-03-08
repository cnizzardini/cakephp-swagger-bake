<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EmployeeTitles Model
 *
 * @property \SwaggerBakeTest\App\Model\Table\EmployeesTable&\Cake\ORM\Association\BelongsTo $Employees
 *
 * @method \SwaggerBakeTest\App\Model\Entity\EmployeeTitle newEmptyEntity()
 * @method \SwaggerBakeTest\App\Model\Entity\EmployeeTitle newEntity(array $data, array $options = [])
 * @method \SwaggerBakeTest\App\Model\Entity\EmployeeTitle[] newEntities(array $data, array $options = [])
 * @method \SwaggerBakeTest\App\Model\Entity\EmployeeTitle get($primaryKey, $options = [])
 * @method \SwaggerBakeTest\App\Model\Entity\EmployeeTitle findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \SwaggerBakeTest\App\Model\Entity\EmployeeTitle patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \SwaggerBakeTest\App\Model\Entity\EmployeeTitle[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \SwaggerBakeTest\App\Model\Entity\EmployeeTitle|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \SwaggerBakeTest\App\Model\Entity\EmployeeTitle saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \SwaggerBakeTest\App\Model\Entity\EmployeeTitle[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \SwaggerBakeTest\App\Model\Entity\EmployeeTitle[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \SwaggerBakeTest\App\Model\Entity\EmployeeTitle[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \SwaggerBakeTest\App\Model\Entity\EmployeeTitle[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EmployeeTitlesTable extends Table
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

        $this->setTable('employee_titles');
        $this->setDisplayField('title');
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
            ->scalar('title')
            ->maxLength('title', 50)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->date('from_date')
            ->requirePresence('from_date', 'create')
            ->notEmptyDate('from_date');

        $validator
            ->date('to_date')
            ->allowEmptyDate('to_date');

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
