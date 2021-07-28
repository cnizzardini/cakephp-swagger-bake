<?php
declare(strict_types=1);

/**
 * @deprecated this class will be removed soon, use SwaggerBake\Lib\SwaggerFactory instead
 */
trigger_deprecation(
    'SwaggerBake\Lib\Factory\SwaggerFactory',
    '2.0.0',
    'this class will be removed soon, use SwaggerBake\Lib\SwaggerFactory instead'
);
class_alias('SwaggerBake\Lib\SwaggerFactory', 'SwaggerBake\Lib\Factory\SwaggerFactory');
