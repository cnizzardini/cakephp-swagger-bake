<?php


namespace SwaggerBake\Test\TestCase\Lib;

use Cake\Routing\Router;
use Cake\Routing\RouteBuilder;
use Cake\TestSuite\TestCase;

use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Swagger;

class SwaggerOperationTest extends TestCase
{
    /**
     * @var string[]
     */
    public $fixtures = [
        'plugin.SwaggerBake.DepartmentEmployees',
        'plugin.SwaggerBake.Departments',
        'plugin.SwaggerBake.Employees',
        'plugin.SwaggerBake.EmployeeSalaries',
    ];

    private Router $router;

    private array $config;

    private Swagger $swagger;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $router = new Router();
        $router::scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'map' => [
                    'customGet' => [
                        'action' => 'customGet',
                        'method' => 'GET',
                        'path' => 'custom-get'
                    ],
                ]
            ]);
            $builder->resources('Departments', function (RouteBuilder $routes) {
                $routes->resources('DepartmentEmployees');
            });
            $builder->resources('EmployeeSalaries');
        });
        $this->router = $router;

        $this->config = [
            'prefix' => '/',
            'yml' => '/config/swagger-bare-bones.yml',
            'json' => '/webroot/swagger.json',
            'webPath' => '/swagger.json',
            'hotReload' => false,
            'exceptionSchema' => 'Exception',
            'requestAccepts' => ['application/x-www-form-urlencoded'],
            'responseContentTypes' => ['application/json'],
            'namespaces' => [
                'controllers' => ['\SwaggerBakeTest\App\\'],
                'entities' => ['\SwaggerBakeTest\App\\'],
                'tables' => ['\SwaggerBakeTest\App\\'],
            ]
        ];

        $configuration = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);
        $cakeRoute = new RouteScanner($this->router, $configuration);
        $this->swagger = new Swagger(new ModelScanner($cakeRoute, $configuration));

        
    }

    public function test_crud_operations_exist(): void
    {
        $arr = json_decode($this->swagger->toString(), true);

        $this->assertArrayHasKey('get', $arr['paths']['/employees']);
        $this->assertArrayHasKey('post', $arr['paths']['/employees']);
        $this->assertArrayHasKey('get', $arr['paths']['/employees/{id}']);
        $this->assertArrayHasKey('patch', $arr['paths']['/employees/{id}']);
        $this->assertArrayHasKey('delete', $arr['paths']['/employees/{id}']);

    }

    public function test_default_response_schema_on_index_method(): void
    {
        $arr = json_decode($this->swagger->toString(), true);

        $employee = $arr['paths']['/employees']['get'];

        $schema =  $employee['responses'][200]['content']['application/json']['schema'];

        $this->assertEquals('array', $schema['type']);
        $this->assertEquals('#/x-swagger-bake/components/schemas/Employee-Read', $schema['items']['$ref']);
    }

    public function test_default_request_schema_on_add_method(): void
    {
        $arr = json_decode($this->swagger->toString(), true);

        $employee = $arr['paths']['/employees']['post'];
        $schema =  $employee['requestBody']['content']['application/x-www-form-urlencoded']['schema'];

        $this->assertEquals('#/x-swagger-bake/components/schemas/Employee-Add', $schema['$ref']);
    }

    public function test_default_response_schema_on_add_method(): void
    {
        $arr = json_decode($this->swagger->toString(), true);

        $employee = $arr['paths']['/employees']['post'];
        $schema =  $employee['responses'][200]['content']['application/json']['schema'];

        $this->assertEquals('#/x-swagger-bake/components/schemas/Employee-Read', $schema['$ref']);
    }

    public function test_default_request_body_schema_on_edit_method(): void
    {
        $arr = json_decode($this->swagger->toString(), true);

        $employee = $arr['paths']['/employees/{id}']['patch'];
        $schema =  $employee['requestBody']['content']['application/x-www-form-urlencoded']['schema'];

        $this->assertEquals('#/x-swagger-bake/components/schemas/Employee-Edit', $schema['$ref']);
    }

    public function test_default_response_schema_on_edit_method(): void
    {
        $arr = json_decode($this->swagger->toString(), true);

        $employee = $arr['paths']['/employees/{id}']['patch'];
        $schema =  $employee['responses'][200]['content']['application/json']['schema'];

        $this->assertEquals('#/x-swagger-bake/components/schemas/Employee-Read', $schema['$ref']);
    }

    public function test_exception_response_schema(): void
    {
        $arr = json_decode($this->swagger->toString(), true);

        $responses = $arr['paths']['/employees/custom-get']['get']['responses'];

        $this->assertArrayHasKey(400, $responses);
        $this->assertArrayHasKey(401, $responses);
        $this->assertArrayHasKey(403, $responses);
        $this->assertArrayHasKey(500, $responses);
    }

    public function test_yml_path_operation_takes_precedence(): void
    {
        $config = $this->config;
        $config['yml'] = '/config/swagger-with-existing.yml';
        $configuration = new Configuration($config, SWAGGER_BAKE_TEST_APP);

        $cakeRoute = new RouteScanner($this->router, $configuration);
        $swagger = new Swagger(new ModelScanner($cakeRoute, $configuration));

        $arr = json_decode($swagger->toString(), true);

        $this->assertArrayHasKey('/employee-salaries', $arr['paths']);
        $this->assertArrayHasKey('get', $arr['paths']['/employee-salaries']);

        $this->assertEquals('phpunit test string', $arr['paths']['/employee-salaries']['get']['description']);
    }

    public function test_security_scheme_from_authentication_component(): void
    {
        $config = $this->config;
        $config['yml'] = '/config/swagger-with-existing.yml';
        $configuration = new Configuration($config, SWAGGER_BAKE_TEST_APP);

        $cakeRoute = new RouteScanner($this->router, $configuration);
        $swagger = new Swagger(new ModelScanner($cakeRoute, $configuration));

        $arr = json_decode($swagger->toString(), true);
        $securities = $arr['paths']['/departments']['get']['security'];
        $security = reset($securities);

        $this->assertEquals('BearerAuth', array_keys($security)[0]);
    }

    public function test_multiple_security_schemes(): void
    {
        $config = $this->config;
        $config['yml'] = '/config/swagger-with-existing.yml';
        $configuration = new Configuration($config, SWAGGER_BAKE_TEST_APP);

        $cakeRoute = new RouteScanner($this->router, $configuration);
        $swagger = new Swagger(new ModelScanner($cakeRoute, $configuration));

        $arr = json_decode($swagger->toString(), true);
        $securities = $arr['paths']['/departments/{id}']['get']['security'];

        $this->assertArrayHasKey('BearerAuth', $securities[0]);
        $this->assertArrayHasKey('ApiKey', $securities[1]);
        $this->assertCount(2, $securities[0]['BearerAuth']);
    }
}