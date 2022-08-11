<?php

namespace SwaggerBake\Test\TestCase;

use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\Command\Command;

class BakeTemplateTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    public $fixtures = [
        'plugin.SwaggerBake.Bakers',
    ];

    /** @var string  */
    private $controller;

    public function setUp() : void
    {
        parent::setUp();
        $this->setAppNamespace('SwaggerBakeTest\App');
        $this->useCommandRunner();

        $this->controller = APP . DS . 'Controller' . DS . 'BakersController.php';

        if (is_file($this->controller)) {
            unlink($this->controller);
        }
    }

    public function test_bake_controller(): void
    {
        $this->exec('bake controller Bakers --no-test --force --theme SwaggerBake');

        $controllerFile = 'BakersController.php';
        $assets = TEST . DS . 'assets' . DS;

        $this->assertOutputContains('Baking controller class for Bakers...');
        $this->assertOutputContains('<success>Wrote</success>');
        $this->assertOutputContains(
            'tests' . DS . 'test_app' . DS . 'src' . DS . 'Controller' . DS . $controllerFile
        );
        $this->assertFileExists($this->controller);
        $this->assertEquals(
            str_replace("\r\n", "\n", file_get_contents($assets . $controllerFile)),
            file_get_contents($this->controller)
        );
    }
}