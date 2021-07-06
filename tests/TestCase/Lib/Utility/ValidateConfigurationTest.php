<?php

namespace SwaggerBake\Test\TestCase\Lib\Utility;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Utility\ValidateConfiguration;

class ValidateConfigurationTest extends TestCase
{
    public function test_validate_method_throws_yml_file_required_exception(): void
    {
        try {
            ValidateConfiguration::validate(
                new Configuration(['yml' => 'nope'], SWAGGER_BAKE_TEST_APP)
            );
        } catch(\LogicException $e) {
            $this->assertStringContainsString(
                ValidateConfiguration::YML_FILE_REQUIRED_ERROR_MSG,
                $e->getMessage()
            );
        }
    }

    public function test_validate_method_throws_yml_file_not_found_exception(): void
    {
        try {
            ValidateConfiguration::validate(
                new Configuration(['yml' => '/tmp/nope.yml'], SWAGGER_BAKE_TEST_APP)
            );
        } catch(\LogicException $e) {
            $this->assertStringContainsString(
                ValidateConfiguration::YML_FILE_NOT_FOUND_ERROR_MSG,
                $e->getMessage()
            );
        }
    }

    public function test_validate_prefix_exception(): void
    {
        try {
            ValidateConfiguration::validate(
                new Configuration(['yml' => '/config/swagger.yml','prefix' => ''], SWAGGER_BAKE_TEST_APP)
            );
        } catch(\LogicException $e) {
            $this->assertStringContainsString(
                'Prefix is required',
                $e->getMessage()
            );
        }
    }

    public function test_unable_to_create_swagger_file_exception(): void
    {
        try {
            ValidateConfiguration::validate(
                new Configuration([
                    'yml' => '/config/swagger.yml',
                    'prefix' => '/',
                    'json' => '../../../../swagger.json'
                ], SWAGGER_BAKE_TEST_APP)
            );
        } catch(SwaggerBakeRunTimeException $e) {
            $this->assertStringContainsString(
                'Unable to create swagger file. Try creating an empty file first or checking permissions',
                $e->getMessage()
            );
        }
    }
}