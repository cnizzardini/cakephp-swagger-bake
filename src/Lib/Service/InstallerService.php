<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Service;

use SwaggerBake\Lib\Exception\InstallException;
use SwaggerBake\Lib\Utility\FileUtility;

class InstallerService
{
    private const ASSETS = __DIR__ . DS . '..' . DS . '..' . DS . '..' . DS . 'assets';
    private FileUtility $fileUtility;

    /**
     * @param string $configDir The userland config dir, typically the value of the CakePHP CONFIG constant
     * @param \SwaggerBake\Lib\Utility\FileUtility|null $fileUtility FileUtility instance
     */
    public function __construct(
        private string $configDir = CONFIG,
        ?FileUtility $fileUtility = null,
    ) {
        $this->fileUtility = $fileUtility ?? new FileUtility();
    }

    /**
     * @param string $prefix The users desired api prefix such as `/` or `/api`
     * @param bool $skipErrors Skip errors which can be skipped
     * @return bool
     * @throws \SwaggerBake\Lib\Exception\InstallException
     */
    public function install(string $prefix, bool $skipErrors = false): bool
    {
        $prefix = trim($prefix);
        $this->validate($prefix, $skipErrors);

        $fromAssets = self::ASSETS . '/swagger.yml';
        $swaggerYml = $this->configDir . 'swagger.yml';
        if (!$this->fileUtility->copy($fromAssets, $swaggerYml)) {
            throw new InstallException(
                "Error copying base OpenAPI YAML from `$fromAssets` to `$swaggerYml`",
            );
        }

        $fromAssets = self::ASSETS . '/swagger_bake.php';
        $swaggerBake = $this->configDir . 'swagger_bake.php';
        if (!$this->fileUtility->copy($fromAssets, $swaggerBake)) {
            throw new InstallException(
                "Error copying swagger_bake config from `$fromAssets` to `$swaggerBake`",
            );
        }

        $contents = $this->fileUtility->getContents($swaggerYml);
        if (!$contents) {
            throw new InstallException("Error reading YAML contents from `$swaggerYml`");
        }

        $contents = str_replace('YOUR-SERVER-HERE', $prefix, $contents);
        if (!$this->fileUtility->putContents($swaggerYml, $contents)) {
            throw new InstallException("Error writing OpenAPI YAML to `$swaggerYml`");
        }

        $contents = $this->fileUtility->getContents($swaggerBake);
        if (!$contents) {
            throw new InstallException("Error reading config contents from `$swaggerBake`");
        }

        $contents = str_replace('/your-relative-api-url', $prefix, $contents);
        if (!$this->fileUtility->putContents($swaggerBake, $contents)) {
            throw new InstallException("Error writing config to `$swaggerBake`");
        }

        return true;
    }

    /**
     * @param string $prefix The users desired api prefix such as `/` or `/api`
     * @param bool $skipErrors Skip errors which can be skipped
     * @return void
     * @throws \SwaggerBake\Lib\Exception\InstallException
     */
    private function validate(string $prefix, bool $skipErrors): void
    {
        if (!str_starts_with($prefix, '/') || !filter_var('http://localhost' . $prefix, FILTER_VALIDATE_URL)) {
            throw new InstallException(
                "Prefix is invalid. Prefix `$prefix` should start with a `/` and be a valid URL path.",
            );
        }

        if (!$this->fileUtility->isDir(self::ASSETS)) {
            throw new InstallException(
                sprintf(
                    'Assets directory `%s` does not exist. Please correct the issue or install manually.',
                    self::ASSETS,
                ),
            );
        }

        $yml = $this->configDir . 'swagger.yml';
        $config = $this->configDir . 'swagger_bake.php';
        if ($skipErrors == false && ($this->fileUtility->fileExists($yml) || $this->fileUtility->fileExists($config))) {
            throw (new InstallException())
                ->setQuestion(
                    'The installer found an existing swagger.yml and/or swagger_bake.php file. ' .
                    'Do you want to continue & overwrite these files?',
                );
        }
    }
}
