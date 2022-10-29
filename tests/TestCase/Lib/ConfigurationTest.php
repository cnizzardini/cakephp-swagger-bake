<?php

namespace SwaggerBake\Test\TestCase\Lib;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Test\TestCase\Helper\ConfigurationHelperTrait;

class ConfigurationTest extends TestCase
{
    use ConfigurationHelperTrait;

    private const DEFAULT_CONFIGS = [
        'prefix' => '/your-relative-api-url',
        'yml' => '/config/swagger.yml',
        'json' => '/webroot/swagger.json',
        'webPath' => '/swagger.json',
    ];

    private Configuration $configuration;

    public function setUp(): void
    {
        parent::setUp();

        $this->configuration = $this->createConfiguration();
    }

    public function test_get_set_docType(): void
    {
        $this->configuration->setDocType('redoc');
        $this->assertEquals('redoc', $this->configuration->getDocType());
    }

    public function test_property_does_not_exist_throws_logic_exception(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches("/Property nope does not exist/");
        $configs = array_merge(self::DEFAULT_CONFIGS, ['nope' => 'nope']);
        new Configuration($configs, SWAGGER_BAKE_TEST_APP);
    }

    public function test_constructor_throws_exception_when_config_arg_missing_required_data(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Configuration(['test']);
        $this->assertStringContainsString('must be defined in your', $this->getExpectedExceptionMessage());
    }

    /**
     * @dataProvider dataProviderInvalidConfig
     *
     * @param string $property The config property
     * @param mixed $value The value of the $property
     * @param string $exceptionClass The expected exception
     * @param string $exceptionMsg A string the exception message should contain
     *
     * @return void
     */
    public function test_invalid_configs_should_throw_exceptions_with_invalid_data(
        string $property,
        mixed $value,
        string $exceptionClass,
        string $exceptionMsg
    ): void {
        if (!class_exists($exceptionClass)) {
            throw new \InvalidArgumentException("$exceptionClass is not a class. Test cannot be run.");
        }
        $this->expectException($exceptionClass);
        $this->expectExceptionMessageMatches("/$exceptionMsg/");
        $configs = array_merge(self::DEFAULT_CONFIGS, [$property => $value]);
        new Configuration($configs, SWAGGER_BAKE_TEST_APP);
    }

    /**
     * Returns a dataProvider containing the property, value, excepted exception clas, and a string the exception
     * message is expected to contain.
     *
     * @return array
     */
    public function dataProviderInvalidConfig(): array
    {
        $invalidPath = '/' . '..' . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS;

        return [
            ['prefix', 'nope', \InvalidArgumentException::class, 'Invalid prefix'],
            ['yml', 'nope', \InvalidArgumentException::class, 'Value should start with'],
            ['yml', $invalidPath . 'nope', \InvalidArgumentException::class, 'yml must exist on the file'],
            ['json', 'nope', \InvalidArgumentException::class, 'Value should start with'],
            ['json', $invalidPath . 'nope', \InvalidArgumentException::class, 'Config value for `json` must exist'],
            ['webPath', 'nope', \InvalidArgumentException::class, 'Invalid webPath'],
            ['docType', 'nope', \InvalidArgumentException::class, 'Invalid docType'],
            ['editActionMethods', ['nope'], \InvalidArgumentException::class, 'Invalid editActionMethod'],
            ['connectionName', 'nope', \InvalidArgumentException::class, 'Invalid connectionName'],
        ];
    }
}