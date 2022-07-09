<?php

namespace SwaggerBake\Test\Traits;

use Cake\Console\TestSuite\Constraint\ContentsContain;
use Cake\TestSuite\ConsoleIntegrationTestTrait;

trait WindowsPathCompatibility
{
    use ConsoleIntegrationTestTrait;

    /**
     * Attempts to assert that expected path is present in the output
     * regardless of running on Windows or Unix.
     * It is assumed that parts of the path will be formatted one way,
     * and the other parts formatted the other way.
     * example: `C:\cakephp\webroot/swagger.json`
     *
     * @param string $expected
     * @param string $message
     * @return void
     */
    public function assertOutputContainsWindowsCompatible(string $expected, string $message = '')
    {
        $this->assertThat(
            $this->convertPathToUnixStandard($expected),
            new ContentsContain($this->convertPathsToUnixStandard($this->_out->messages()), 'output'),
            $message
        );
    }

    /**
     * @param string $input
     * @return string
     */
    public function convertPathToUnixStandard(string $input): string
    {
        return str_replace('\\', '/', $input);
    }

    /**
     * @param string[] $input
     * @return string[]
     */
    public function convertPathsToUnixStandard(array $input): array
    {
        array_walk($input, function (string &$input) {
            $input = $this->convertPathToUnixStandard($input);
        });

        return $input;
    }
}